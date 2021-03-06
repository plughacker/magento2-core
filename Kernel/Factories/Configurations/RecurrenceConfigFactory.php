<?php

namespace PlugHacker\PlugCore\Kernel\Factories\Configurations;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use PlugHacker\PlugCore\Kernel\ValueObjects\Configuration\RecurrenceConfig;

class RecurrenceConfigFactory implements FactoryCreateFromDbDataInterface
{
    /**
     * @param array $data
     * @return AbstractEntity|RecurrenceConfig
     */
    public function createFromDbData($data)
    {

        $recurrenceConfig = new RecurrenceConfig();

        if (isset($data->enabled)) {
            $recurrenceConfig->setEnabled((bool) $data->enabled);
        }

        if (isset($data->showRecurrenceCurrencyWidget)) {
            $recurrenceConfig->setShowRecurrenceCurrencyWidget(
                (bool) $data->showRecurrenceCurrencyWidget
            );
        }

        if (isset($data->purchaseRecurrenceProductWithNormalProduct)) {
            $recurrenceConfig->setPurchaseRecurrenceProductWithNormalProduct(
                (bool) $data->purchaseRecurrenceProductWithNormalProduct
            );
        }

        if (isset($data->conflictMessageRecurrenceProductWithNormalProduct)) {
            $recurrenceConfig->setConflictMessageRecurrenceProductWithNormalProduct(
                $data->conflictMessageRecurrenceProductWithNormalProduct
            );
        }

        if (isset($data->purchaseRecurrenceProductWithRecurrenceProduct)) {
            $recurrenceConfig->setPurchaseRecurrenceProductWithRecurrenceProduct(
                $data->purchaseRecurrenceProductWithRecurrenceProduct
            );
        }

        if (isset($data->conflictMessageRecurrenceProductWithRecurrenceProduct)) {
            $recurrenceConfig->setConflictMessageRecurrenceProductWithRecurrenceProduct(
                $data->conflictMessageRecurrenceProductWithRecurrenceProduct
            );
        }

        if (isset($data->decreaseStock)) {
            $recurrenceConfig->setDecreaseStock($data->decreaseStock);
        }

        return $recurrenceConfig;
    }
}
