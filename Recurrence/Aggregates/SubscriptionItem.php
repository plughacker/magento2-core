<?php

namespace PlugHacker\PlugCore\Recurrence\Aggregates;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\SubscriptionId;

class SubscriptionItem extends AbstractEntity
{

    /** @var SubscriptionId */
    private $subscriptionId;

    /** @var string */
    private $code;

    /** @var integer */
    private $quantity;

    private $createdAt;
    private $updatedAt;

    /**
     * @return SubscriptionId
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @param  SubscriptionId $subscriptionId
     * @return $this
     */
    public function setSubscriptionId(SubscriptionId $subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param  string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $quantity;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->getId(),
            "plugId" => $this->getPlugId(),
            "subscriptionId" => $this->getSubscriptionId(),
            "code" => $this->getCode(),
            "quantity" => $this->getQuantity()
        ];
    }
}
