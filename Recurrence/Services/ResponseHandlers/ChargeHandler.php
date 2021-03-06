<?php

namespace PlugHacker\PlugCore\Recurrence\Services\ResponseHandlers;

use PlugHacker\PlugCore\Kernel\Aggregates\Charge;
use PlugHacker\PlugCore\Kernel\Aggregates\Order;
use PlugHacker\PlugCore\Kernel\Interfaces\ChargeInterface;
use PlugHacker\PlugCore\Kernel\Repositories\ChargeRepository;
use PlugHacker\PlugCore\Kernel\Repositories\OrderRepository;
use PlugHacker\PlugCore\Kernel\Services\LocalizationService;
use PlugHacker\PlugCore\Kernel\Services\MoneyService;
use PlugHacker\PlugCore\Kernel\Services\OrderService;
use PlugHacker\PlugCore\Kernel\ValueObjects\ChargeStatus;

final class ChargeHandler extends AbstractResponseHandler
{
    /**
     * @param Order $createdOrder
     * @return mixed
     */
    public function handle(Charge $charge, $order)
    {
        $chargeStatus = ucfirst($charge->getStatus()->getStatus());
        $statusHandler = 'handleChargeStatus' . $chargeStatus;

        $this->logService->orderInfo(
            $charge->getCode(),
            "Handling subscription status: $chargeStatus"
        );

        $this->$statusHandler($charge, $order);
    }

    /**
     * @param Order $order
     * @return bool|string|null
     */
    private function handleChargeStatusPaid(Charge $charge, $order)
    {
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        $transaction = $charge->getTransactions()[0];

        //$platformOrder = $order->getPlatformOrder();

        $charge->addTransaction($transaction);
        $paidAmount = $transaction->getPaidAmount();

        if (!$charge->getStatus()->equals(ChargeStatus::paid())) {
            $charge->pay($paidAmount);
        }

        if ($charge->getPaidAmount() == 0) {
            $charge->setPaidAmount($paidAmount);
        }

        $history = $this->prepareHistoryComment($charge);
        $order->addHistoryComment($history);

        $orderService->syncPlatformWith($order, false);

        $order->save();
    }

    public function prepareHistoryComment(ChargeInterface $charge)
    {
        $i18n = new LocalizationService();
        $moneyService = new MoneyService();

        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $moneyService->centsToFloat($charge->getPaidAmount());

            $history = $i18n->getDashboard(
                'Payment received: %.2f',
                $amountInCurrency
            );

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $history .= ". " . $i18n->getDashboard(
                        "Extra amount paid: %.2f",
                        $moneyService->centsToFloat($extraValue)
                    );
            }

            if ($extraValue < 0) {
                $history .= ". " . $i18n->getDashboard(
                        "Remaining amount: %.2f",
                        $moneyService->centsToFloat(abs($extraValue))
                    );
            }

            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $history = $i18n->getDashboard(
                    'Refunded amount: %.2f',
                    $moneyService->centsToFloat($refundedAmount)
                );
                $history .= " (" . $i18n->getDashboard('until now') . ")";
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $moneyService->centsToFloat($canceledAmount);

                $history .= " ({$i18n->getDashboard('Partial Payment')}";
                $history .= ". " .
                    $i18n->getDashboard(
                        'Canceled amount: %.2f',
                        $amountCanceledInCurrency
                    ) . ')';
            }

            return $history;
        }

        $amountInCurrency = $moneyService->centsToFloat($charge->getRefundedAmount());
        $history = $i18n->getDashboard(
            'Charge canceled.'
        );

        $history .= ' ' . $i18n->getDashboard(
                'Refunded amount: %.2f',
                $amountInCurrency
            );
        $history .= " (" . $i18n->getDashboard('until now') . ")";

        return $history;
    }
}
