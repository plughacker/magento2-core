<?php

namespace PlugHacker\PlugCore\Kernel\Factories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Aggregates\Transaction;
use PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryInterface;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\ChargeId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\TransactionId;
use PlugHacker\PlugCore\Kernel\ValueObjects\TransactionStatus;
use PlugHacker\PlugCore\Kernel\ValueObjects\TransactionType;
use PlugHacker\PlugCore\Payment\Factories\SavedCardFactory;

class TransactionFactory implements FactoryInterface
{
    public function createFromPostData($postData)
    {
        $transaction = new Transaction;

        if (isset($postData['webhook'])) {
            $transactionRequest = array_last($postData['transactionRequests']);

            $transaction->setId($postData['orderId']);
            $transaction->setPlugId(new TransactionId($transactionRequest['id']));
        } else {
            $transaction->setPlugId(new TransactionId($postData['id']));
        }

        $baseStatus = explode('_', $postData['status']);
        $status = $baseStatus[0];
        for ($i = 1, $iMax = count($baseStatus); $i < $iMax; $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        if (!method_exists(TransactionStatus::class, $status)) {
            throw new InvalidParamException(
                "$status is not a valid TransactionStatus!",
                $status
            );
        }
        $transaction->setStatus(TransactionStatus::$status());

        $type = $postData['paymentType'] ?? '';

        if (empty($type)) {
            $type = 'credit';

            $transactionRequest = array_last($postData['transactionRequests']);

            if (isset($transactionRequest['boleto']) && $transactionRequest['boleto']) {
                $type = 'boleto';
            } elseif (isset($transactionRequest['pix']) && $transactionRequest['pix']) {
                $type = 'pix';
            }
        }

        if (!method_exists(TransactionType::class, $type)) {
            throw new InvalidParamException(
                "$type is not a valid TransactionType!",
                $type
            );
        }
        $transaction->setTransactionType(TransactionType::$type());

        if (isset($postData['amount'])) {
            $transaction->setAmount($postData['amount']);
        }

        $paidAmountIndex = isset($postData['paid_amount']) ? 'paid_amount' : 'amount';
        if (isset($postData[$paidAmountIndex])) {
            $transaction->setPaidAmount($postData[$paidAmountIndex]);
        }

        $acquirerName = '';
        if (isset($postData['acquirer_name'])) {
            $acquirerName = $postData['acquirer_name'];
        }

        $acquirerMessage = '';
        if (isset($postData['acquirer_message'])) {
            $acquirerMessage = $postData['acquirer_message'];
        }

        $acquirerNsu = 0;
        if (isset($postData['acquirer_nsu'])) {
            $acquirerNsu = $postData['acquirer_nsu'];
        }

        $acquirerTid = 0;
        if (isset($postData['acquirer_tid'])) {
            $acquirerTid = $postData['acquirer_tid'];
        }

        $acquirerAuthCode = 0;
        if (isset($postData['acquirer_auth_code'])) {
            $acquirerAuthCode = $postData['acquirer_auth_code'];
        }

        $transaction->setAcquirerName($acquirerName);
        $transaction->setAcquirerMessage($acquirerMessage);
        $transaction->setAcquirerNsu($acquirerNsu);
        $transaction->setAcquirerTid($acquirerTid);
        $transaction->setAcquirerAuthCode($acquirerAuthCode);

        $createdAt = \DateTime::createFromFormat(
            'Y-m-d\TH:i:s',
            substr($postData['createdAt'], 0, 19)
        );

        $transaction->setCreatedAt($createdAt);
        $brand = null;
        $installments = null;
        if (isset($postData['card'])) {
            $brand = $postData['card']['brand'];
            $installments = isset($postData['installments']) ? $postData['installments'] : 1;

            $transaction->setBrand($brand);
            $transaction->setInstallments($installments);
        }

        if (isset($postData['pdf'])) {
            $transaction->setBoletoUrl($postData['pdf']);
        }

        $transaction->setPostData(json_decode(json_encode($postData)));

        if (isset($postData['card'])) {
            $cardFactory = new SavedCardFactory();
            $card = $cardFactory->createFromTransactionData($postData['card']);

            $transaction->setCardData($card);
        }

        return $transaction;
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        $transaction = new Transaction();

        $transaction->setId($dbData['id']);
        $transaction->setChargeId(new ChargeId($dbData['charge_id']));
        $transaction->setPlugId(new TransactionId($dbData['plug_id']));

        $transaction->setAmount($dbData['amount']);
        $transaction->setPaidAmount($dbData['paid_amount']);

        $transaction->setAcquirerName($dbData['acquirer_name']);
        $transaction->setAcquirerMessage($dbData['acquirer_message']);
        $transaction->setAcquirerNsu($dbData['acquirer_nsu']);
        $transaction->setAcquirerTid($dbData['acquirer_tid']);
        $transaction->setAcquirerAuthCode($dbData['acquirer_auth_code']);

        $baseStatus = explode('_', $dbData['status']);
        $status = $baseStatus[0];
        for ($i = 1, $iMax = count($baseStatus); $i < $iMax; $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        if (!method_exists(TransactionStatus::class, $status)) {
            throw new InvalidParamException(
                "$status is not a valid TransactionStatus!",
                $status
            );
        }
        $transaction->setStatus(TransactionStatus::$status());

        $baseType = explode('_', $dbData['type']);
        $type = $baseType[0];
        for ($i = 1, $iMax = count($baseType); $i < $iMax; $i++) {
            $type .= ucfirst(($baseType[$i]));
        }

        if (!method_exists(TransactionType::class, $type)) {
            throw new InvalidParamException(
                "$type is not a valid TransactionType!",
                $type
            );
        }
        $transaction->setTransactionType(TransactionType::$type());

        $createdAt = \DateTime::createFromFormat('Y-m-d H:i:s', $dbData['created_at']);
        $transaction->setCreatedAt($createdAt);

        if (isset($dbData['boleto_url'])) {
            $transaction->setBoletoUrl($dbData['boleto_url']);
        }

        $transaction->setPostData(json_decode(json_encode($dbData)));

        if (!empty($dbData['card_data']) && $dbData['card_data'] !== "null") {
            $cardData = json_decode($dbData['card_data']);
            $cardFactory = new SavedCardFactory();
            $card = $cardFactory->createFromTransactionJson($cardData);

            $transaction->setCardData($card);
        }

        return $transaction;
    }
}
