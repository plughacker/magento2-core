<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractValueObject;

final class TransactionStatus extends AbstractValueObject
{
    const PENDING = 'pending';
    const PRE_AUTHORIZED = 'pre_authorized';
    const AUTHORIZED = 'authorized';
    const CAPTURE = "capture";
    const PARTIAL_CAPTURE = "partial_capture";
    const AUTHORIZED_PENDING_CAPTURE = 'authorized_pending_capture';
    const VOIDED = 'voided';

    const CHARGED_BACK = 'charged_back';
    const REFUNDED = 'refunded';
    const PARTIAL_VOID = 'partial_void';
    const WITH_ERROR = 'withError';
    const NOT_AUTHORIZED = 'notAuthorized';
    const FAILED = 'failed';
    const SUCCESS = 'success';

    const GENERATED = 'generated';
    const UNDERPAID = 'underpaid';
    const PAID = 'paid';
    const OVERPAID = 'overpaid';
    const PARTIAL_REFUNDED = 'partial_refunded';
    const WAITING_PAYMENT = 'waiting_payment';
    const PENDING_REFUND = 'pending_refund';
    const EXPIRED = 'expired';
    const PENDING_REVIEW = 'pending_review';
    const ANALYZING = 'analyzing';
    const WAITING_CAPTURE = 'waiting_capture';
    const CANCELED = 'canceled';

    /**
     *
     * @var string
     */
    private $status;

    /**
     * OrderStatus constructor.
     *
     * @param string $status
     */
    private function __construct($status)
    {
        $this->setStatus($status);
    }

    public static function partialRefunded()
    {
        return new self(self::PARTIAL_REFUNDED);
    }

    public static function partialCapture()
    {
        return new self(self::PARTIAL_CAPTURE);
    }

    public static function pending()
    {
        return new self(self::PENDING);
    }

    public static function preAuthorized()
    {
        return new self(self::AUTHORIZED);
    }

    public static function authorized()
    {
        return new self(self::AUTHORIZED);
    }

    public static function capture()
    {
        return new self(self::CAPTURE);
    }

    public static function authorizedPendingCapture()
    {
        return new self(self::AUTHORIZED_PENDING_CAPTURE);
    }

    public static function voided()
    {
        return new self(self::VOIDED);
    }

    public static function chargedBack()
    {
        return new self(self::CHARGED_BACK);
    }

    public static function partialVoid()
    {
        return new self(self::PARTIAL_VOID);
    }

    public static function generated()
    {
        return new self(self::GENERATED);
    }

    public static function underpaid()
    {
        return new self(self::UNDERPAID);
    }

    public static function paid()
    {
        return new self(self::PAID);
    }

    public static function success()
    {
        return new self(self::SUCCESS);
    }

    public static function overpaid()
    {
        return new self(self::OVERPAID);
    }

    public static function withError()
    {
        return new self(self::WITH_ERROR);
    }

    public static function notAuthorized()
    {
        return new self(self::NOT_AUTHORIZED);
    }

    public static function refunded()
    {
        return new self(self::REFUNDED);
    }

    public static function failed()
    {
        return new self(self::FAILED);
    }

    public static function waitingPayment()
    {
        return new self(self::WAITING_PAYMENT);
    }

    public static function pendingRefund()
    {
        return new self(self::PENDING_REFUND);
    }

    public static function expired()
    {
        return new self(self::EXPIRED);
    }

    public static function pendingReview()
    {
        return new self(self::PENDING_REVIEW);
    }

    public static function analyzing()
    {
        return new self(self::ANALYZING);
    }

    public static function waitingCapture()
    {
        return new self(self::WAITING_CAPTURE);
    }

    public static function canceled()
    {
        return new self(self::CANCELED);
    }

    /**
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @param  string $status
     * @return OrderStatus
     */
    private function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  OrderStatus $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->getStatus() === $object->getStatus();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    public function jsonSerialize(): mixed
    {
        return $this->status;
    }
}
