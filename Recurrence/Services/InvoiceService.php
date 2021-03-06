<?php

namespace PlugHacker\PlugCore\Recurrence\Services;

use PlugHacker\PlugCore\Kernel\Services\APIService;
use PlugHacker\PlugCore\Kernel\Services\LogService;
use PlugHacker\PlugCore\Kernel\ValueObjects\ChargeStatus;
use PlugHacker\PlugCore\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use PlugHacker\PlugCore\Recurrence\Aggregates\Invoice;
use PlugHacker\PlugCore\Recurrence\Factories\ChargeFactory;
use PlugHacker\PlugCore\Recurrence\Factories\InvoiceFactory;
use PlugHacker\PlugCore\Recurrence\Repositories\ChargeRepository;
use PlugHacker\PlugCore\Recurrence\ValueObjects\InvoiceStatus;

class InvoiceService
{
    private $logService;
    /**
     * @var LocalizationService
     */
    private $i18n;
    private $apiService;

    public function __construct()
    {

    }

    public function getById($invoiceId)
    {

    }

    public function cancel($invoiceId)
    {
        try {
            $logService = $this->getLogService();
            $charge = $this->getChargeRepository()
                ->findByInvoiceId($invoiceId);

            if (!$charge) {
                $message = 'Invoice not found';

                $logService->info(
                    null,
                    $message . " ID {$invoiceId} ."
                );

                //Code 404
                throw new \Exception($message, 404);
            }

            if ($charge->getStatus()->getStatus() == InvoiceStatus::canceled()->getStatus()) {
                $message = 'Invoice already canceled';

                return [
                    "message" => $message,
                    "code" => 200
                ];
            }
            $invoiceFactory = new InvoiceFactory();
            $invoice = $invoiceFactory->createFromCharge($charge);

            $result = $this->cancelInvoiceAtPlug($invoice);

            $return = [
                "message" => 'Invoice canceled successfully',
                "code" => 200
            ];

            $logService->info(
                null,
                'Invoice cancel response: ' . $return['message']
            );

            $chargeResult = $result->charge;

            $charge->setStatus(ChargeStatus::canceled());

            if (isset($chargeResult->canceledAmount)) {
                $charge->setCanceledAmount($chargeResult->canceledAmount);
            }

            if (isset($chargeResult->paidAmount)) {
                $charge->setPaidAmount($chargeResult->paidAmount);
            }

            /**
             * @todo Add canceled_at to charge
             */

            $this->getChargeRepository()->save($charge);

            return $return;

        } catch (\Exception $exception) {
            $logService = $this->getLogService();

            $logService->info(
                null,
                $exception->getMessage()
            );

            throw $exception;
        }
    }

    public function cancelInvoiceAtPlug(Invoice $invoice)
    {
        $logService = $this->getLogService();
        $apiService = $this->getApiService();

        $logService->info(
            null,
            'Invoice cancel request | invoice id: ' .
            $invoice->getPlugId()->getValue()
        );

        return $apiService->cancelInvoice($invoice);
    }

    public function getApiService()
    {
        return new APIService();
    }

    public function getLogService()
    {
        return new LogService('InvoiceService', true);
    }

    public function getChargeRepository()
    {
        return new ChargeRepository();
    }
}
