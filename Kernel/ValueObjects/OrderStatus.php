<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractValueObject;

final class OrderStatus extends AbstractValueObject
{
    const PAID = 'paid';
    const PENDING = 'pending';
    const PRE_AUTHORIZED = 'pre_authorized';
    const AUTHORIZED = 'authorized';
    const PROCESSING = 'processing';
    const CANCELED = 'canceled';
    const CLOSED = 'closed';
    const FAILED = 'failed';
    const VOIDED = 'voided';

    const CHARGED_BACK = 'charged_back';

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

    static public function paid()
    {
        return new self(self::PAID);
    }

    static public function processing()
    {
        return new self(self::PROCESSING);
    }

    static public function pending()
    {
        return new self(self::PENDING);
    }

    static public function preAuthorized()
    {
        return new self(self::PRE_AUTHORIZED);
    }

    static public function authorized()
    {
        return new self(self::AUTHORIZED);
    }

    static public function pendingPayment()
    {
        return self::pending();
    }

    static public function canceled()
    {
        return new self(self::CANCELED);
    }

    static public function closed()
    {
        return new self(self::CLOSED);
    }

    static public function failed()
    {
        return new self(self::FAILED);
    }

    static public function voided()
    {
        return new self(self::VOIDED);
    }

    static public function chargedBack()
    {
        return new self(self::CHARGED_BACK);
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
        return $this->getStatus();
    }
}
