<?php

namespace PlugHacker\PlugCore\Payment\Services\ResponseHandlers;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDataService;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Aggregates\Order;
use PlugHacker\PlugCore\Kernel\Interfaces\PlatformOrderInterface;
use PlugHacker\PlugCore\Kernel\Repositories\OrderRepository;
use PlugHacker\PlugCore\Kernel\Services\InvoiceService;
use PlugHacker\PlugCore\Kernel\Services\LocalizationService;
use PlugHacker\PlugCore\Kernel\Services\OrderService;
use PlugHacker\PlugCore\Kernel\ValueObjects\InvoiceState;
use PlugHacker\PlugCore\Kernel\ValueObjects\OrderState;
use PlugHacker\PlugCore\Kernel\ValueObjects\OrderStatus;
use PlugHacker\PlugCore\Kernel\ValueObjects\TransactionType;
use PlugHacker\PlugCore\Payment\Aggregates\Order as PaymentOrder;
use PlugHacker\PlugCore\Payment\Factories\SavedCardFactory;
use PlugHacker\PlugCore\Payment\Repositories\CustomerRepository;
use PlugHacker\PlugCore\Payment\Repositories\SavedCardRepository;
use PlugHacker\PlugCore\Kernel\Aggregates\Charge;
use PlugHacker\PlugCore\Payment\Services\CardService;
use PlugHacker\PlugCore\Payment\Services\CustomerService;

/** For possible order states, see https://docs.plug.com/v1/reference#pedidos */
final class OrderHandler extends AbstractResponseHandler
{
    /**
     * @param Order $createdOrder
     * @return mixed
     */
    public function handle($createdOrder, PaymentOrder $paymentOrder = null)
    {
        $orderStatus = ucfirst($createdOrder->getStatus()->getStatus());
        $statusHandler = 'handleOrderStatus' . $orderStatus;

        $this->logService->orderInfo(
            $createdOrder->getCode(),
            "Handling order status: $orderStatus"
        );

        $orderRepository = new OrderRepository();
        $orderRepository->save($createdOrder);

        /*$customerService = new CustomerService();
        $customerService->saveCustomer($createdOrder->getCustomer());*/

        return $this->$statusHandler($createdOrder);
    }

    private function handleOrderStatusProcessing(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();

        $i18n = new LocalizationService();

        $messageComplementEmail = $i18n->getDashboard(
            'New order status: %s',
            $platformOrder->getStatus()
        );

        $sender = $platformOrder->sendEmail($messageComplementEmail);

        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Order waiting for online retries at Plug.' .
                ' PlugId: ' . $order->getPlugId()->getValue()
            ),
            $sender
        );

        return $this->handleOrderStatusPending($order);
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function handleOrderStatusPending(Order $order)
    {
        $this->createAuthorizationTransaction($order);

        $order->setStatus(OrderStatus::pending());
        $platformOrder = $order->getPlatformOrder();

        $i18n = new LocalizationService();

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $statusOrderLabel = $platformOrder->getStatusLabel(
            $order->getStatus()
        );

        $messageComplementEmail = $i18n->getDashboard(
            'New order status: %s',
            $statusOrderLabel
        );

        $sender = $platformOrder->sendEmail($messageComplementEmail);

        $platformOrder->addAdditionalInformation($order->getCharges());

        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Order pending at Plug. Id: %s',
                $order->getPlugId()->getValue()
            ),
            $sender
        );

        return true;
    }

    /**
     * @param Order $order
     * @return bool|string|null
     */
    private function handleOrderStatusPaid(Order $order)
    {
        $invoiceService = new InvoiceService();
        $cardService = new CardService();

        $cantCreateReason = $invoiceService->getInvoiceCantBeCreatedReason($order);
        $invoice = $invoiceService->createInvoiceFor($order);
        if ($invoice !== null) {
            // create payment service to complete payment
            $this->completePayment($order, $invoice);

            $cardService->saveCards($order);

            return true;
        }
        return $cantCreateReason;
    }

    /**
     * @param Order $order
     * @param $invoice
     */
    private function completePayment(Order $order, $invoice)
    {
        $invoice->setState(InvoiceState::paid());
        $invoice->save();
        $platformOrder = $order->getPlatformOrder();

        $this->createCaptureTransaction($order);

        $order->setStatus(OrderStatus::processing());
        //@todo maybe an Order Aggregate should have a State too.
        $platformOrder->setState(OrderState::processing());

        $i18n = new LocalizationService();

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $statusOrderLabel = $platformOrder->getStatusLabel(
            $order->getStatus()
        );

        $messageComplementEmail = $i18n->getDashboard(
            'New order status: %s',
            $statusOrderLabel
        );

        $sender = $platformOrder->sendEmail($messageComplementEmail);

        $platformOrder->addAdditionalInformation($order->getCharges());

        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Order paid.') .
            ' PlugId: ' . $order->getPlugId()->getValue(),
            $sender
        );
    }

    private function createCaptureTransaction(Order $order)
    {
        $dataServiceClass =
            MPSetup::get(MPSetup::CONCRETE_DATA_SERVICE);

        $this->logService->orderInfo(
            $order->getCode(),
            "Creating Capture Transaction..."
        );

        /**
         *
         * @var AbstractDataService $dataService
         */
        $dataService = new $dataServiceClass();
        $dataService->createCaptureTransaction($order);

        $this->logService->orderInfo(
            $order->getCode(),
            "Capture Transaction created."
        );
    }

    private function createAuthorizationTransaction(Order $order)
    {
        $dataServiceClass =
            MPSetup::get(MPSetup::CONCRETE_DATA_SERVICE);

        $this->logService->orderInfo(
            $order->getCode(),
            "Creating Authorization Transaction..."
        );

        /**
         *
         * @var AbstractDataService $dataService
         */
        $dataService = new $dataServiceClass();
        $dataService->createAuthorizationTransaction($order);

        $this->logService->orderInfo(
            $order->getCode(),
            "Authorization Transaction created."
        );
    }

    private function handleOrderStatusCanceled(Order $order)
    {
        return $this->handleOrderStatusFailed($order);
    }

    private function handleOrderStatusFailed(Order $order)
    {
        $charges = $order->getCharges();

        $acquirerMessages = '';
        $historyData = [];
        foreach ($charges as $charge) {
            $transactionRequests = $charge->getTransactionRequests();
            $acquirerMessages .=
                "{$charge->getPlugId()->getValue()} => '{$transactionRequests->getAcquirerMessage()}', ";
            $historyData[$charge->getPlugId()->getValue()] = $transactionRequests->getAcquirerMessage();

        }
        $acquirerMessages = rtrim($acquirerMessages, ', ');

        $this->logService->orderInfo(
            $order->getCode(),
            "Order creation Failed: $acquirerMessages"
        );

        $i18n = new LocalizationService();
        $historyComment = $i18n->getDashboard('Order payment failed');
        $historyComment .= ' (' . $order->getPlugId()->getValue() . ') : ';

        foreach ($historyData as $chargeId => $acquirerMessage) {
            $historyComment .= "$chargeId => $acquirerMessage; ";
        }
        $historyComment = rtrim($historyComment, '; ');
        $order->getPlatformOrder()->addHistoryComment(
            $historyComment
        );

        $order->setStatus(OrderStatus::canceled());
        $order->getPlatformOrder()->setState(OrderState::canceled());
        $order->getPlatformOrder()->save();

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $platformOrder = $order->getPlatformOrder();

        $statusOrderLabel = $platformOrder->getStatusLabel(
            $order->getStatus()
        );

        $messageComplementEmail = $i18n->getDashboard(
            'New order status: %s',
            $statusOrderLabel
        );

        $sender = $platformOrder->sendEmail($messageComplementEmail);

        $order->getPlatformOrder()->addHistoryComment(
            $i18n->getDashboard('Order canceled.'),
            $sender
        );

        return "One or more charges weren't authorized. Please try again.";
    }
}
