<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractValueObject;

final class ChargeStatus extends AbstractValueObject
{
    const PAID = 'paid';
    const PENDING = 'pending';
    const AUTHORIZED = 'authorized';
    const PRE_AUTHORIZED = 'pre_authorized';
    const CAPTURE = 'capture';
    const CANCELED = 'canceled';
    const PROCESSING = 'processing';
    const FAILED = 'failed';

    const UNDERPAID = 'underpaid';
    const OVERPAID = 'overpaid';
    const VOIDED = 'voided';

    /**
     *
     * @var string
     */
    private $status;

    /**
     * ChargeStatus constructor.
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

    static public function authorization()
    {
        return new self(self::AUTHORIZATION);
    }

    static public function authorized()
    {
        return new self(self::AUTHORIZED);
    }

    static public function preAuthorized()
    {
        return new self(self::PRE_AUTHORIZED);
    }

    static public function pending()
    {
        return new self(self::PENDING);
    }

    static public function canceled()
    {
        return new self(self::CANCELED);
    }

    static public function underpaid()
    {
        return new self(self::UNDERPAID);
    }

    static public function overpaid()
    {
        return new self(self::OVERPAID);
    }

    static public function processing()
    {
        return new self(self::PROCESSING);
    }

    static public function failed()
    {
        return new self(self::FAILED);
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
     * @return ChargeStatus
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
     * @param  ChargeStatus $object
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
