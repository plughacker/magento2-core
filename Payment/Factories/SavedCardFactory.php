<?php

namespace PlugHacker\PlugCore\Payment\Factories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryInterface;
use PlugHacker\PlugCore\Kernel\ValueObjects\CardBrand;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\CustomerId;
use PlugHacker\PlugCore\Kernel\ValueObjects\NumericString;
use PlugHacker\PlugCore\Payment\Aggregates\SavedCard;
use PlugHacker\PlugCore\Payment\ValueObjects\CardId;

class SavedCardFactory implements FactoryInterface
{
    /**
     *
     * @param  \stdClass $postData
     * @return SavedCard
     */
    public function createFromPostData($postData)
    {
        $savedCard = new SavedCard();

        $savedCard->setPlugId(
            new CardId($postData->id)
        );

        $savedCard->setOwnerId(
            new CustomerId($postData->owner)
        );

        $brand = strtolower($postData->brand);
        $savedCard->setBrand(CardBrand::$brand());
        $savedCard->setOwnerName($postData->holder_name);
        $savedCard->setFirstSixDigits(
            new NumericString($postData->first_six_digits)
        );
        $savedCard->setLastFourDigits(
            new NumericString($postData->last_four_digits)
        );

        if (isset($postData->created_at)) {
            $createdAt = new \Datetime($postData->created_at);
            $createdAt->setTimezone(MPSetup::getStoreTimezone());
            $savedCard->setCreatedAt($createdAt);
        }

        return $savedCard;
    }

    /**
     *
     * @param  array $dbData
     * @return SavedCard
     */
    public function createFromDbData($dbData)
    {
        $savedCard = new SavedCard();

        $savedCard->setId($dbData['id']);

        $savedCard->setPlugId(
            new CardId($dbData['plug_id'])
        );

        $savedCard->setOwnerId(
            new CustomerId($dbData['owner_id'])
        );

        $brand = strtolower($dbData['brand']);
        $savedCard->setOwnerName($dbData['owner_name']);
        $savedCard->setBrand(CardBrand::$brand());
        $savedCard->setFirstSixDigits(
            new NumericString($dbData['first_six_digits'])
        );
        $savedCard->setLastFourDigits(
            new NumericString($dbData['last_four_digits'])
        );

        if (!empty($dbData['created_at'])) {
            $createdAt = \Datetime::createFromFormat(
                SavedCard::DATE_FORMAT,
                $dbData['created_at']
            );
            $savedCard->setCreatedAt($createdAt);
        }

        return $savedCard;
    }

    /**
     *
     * @param  array $transactionData
     * @return SavedCard
     */
    public function createFromTransactionData($data)
    {
        $savedCard = new SavedCard();

        $savedCard->setPlugId(
            new CardId($data['id'])
        );

        $brand = strtolower($data['brand']);
        $savedCard->setOwnerName($data['holder_name']);
        $savedCard->setBrand(CardBrand::$brand());
        $savedCard->setFirstSixDigits(
            new NumericString($data['first_six_digits'])
        );
        $savedCard->setLastFourDigits(
            new NumericString($data['last_four_digits'])
        );

        if (!empty($data['created_at'])) {
            $createdAt = new \Datetime($data['created_at']);
            $savedCard->setCreatedAt($createdAt);
        }

        return $savedCard;
    }

    /**
     *
     * @param  array $transactionData
     * @return SavedCard
     */
    public function createFromTransactionJson($cardObj)
    {
        $savedCard = new SavedCard();

        $savedCard->setPlugId(
            new CardId($cardObj->plugId)
        );

        if (!empty($cardObj->owner)) {
            $savedCard->setOwnerId(
                new CustomerId($cardObj->owner)
            );
        }

        $brand = strtolower($cardObj->brand);
        $savedCard->setOwnerName($cardObj->ownerName);
        $savedCard->setBrand(CardBrand::$brand());
        $savedCard->setFirstSixDigits(
            new NumericString($cardObj->firstSixDigits)
        );
        $savedCard->setLastFourDigits(
            new NumericString($cardObj->lastFourDigits)
        );

        if (!empty($cardObj->createdAt)) {
            $createdAt = new \Datetime($cardObj->createdAt);
            $savedCard->setCreatedAt($createdAt);
        }

        return $savedCard;
    }
}
