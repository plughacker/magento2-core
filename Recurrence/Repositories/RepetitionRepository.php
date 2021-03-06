<?php

namespace PlugHacker\PlugCore\Recurrence\Repositories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDatabaseDecorator;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;
use PlugHacker\PlugCore\Recurrence\Factories\RepetitionFactory;

class RepetitionRepository extends AbstractRepository
{

    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);

        $query = "
            INSERT INTO $table (
                `subscription_id`,
                `interval`,
                `interval_count`,
                `recurrence_price`,
                `cycles`
            ) VALUES (
                '{$object->getSubscriptionId()}',
                '{$object->getInterval()}',
                '{$object->getIntervalCount()}',
                '{$object->getRecurrencePrice()}',
                '{$object->getCycles()}'
            )
        ";

        return $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);

        $query = "
            UPDATE $table SET
                `subscription_id` = '{$object->getSubscriptionId()}',
                `interval` = '{$object->getInterval()}',
                `interval_count` = '{$object->getIntervalCount()}',
                `recurrence_price` = '{$object->getRecurrencePrice()}',
                `cycles` = '{$object->getCycles()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);

        $query = "DELETE FROM $table WHERE id = {$object->getId()}";

        $this->db->query($query);
    }

    public function deleteBySubscriptionId($subscriptionProductId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);

        $query = "DELETE FROM $table WHERE subscription_id = {$subscriptionProductId}";

        $this->db->query($query);
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);

        $query = "SELECT * FROM $table WHERE id = $objectId";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $repetitionFactory = new RepetitionFactory();
        $repetition = $repetitionFactory->createFromDbData($result->row);

        return $repetition;
    }

    public function findByPlugId(AbstractValidString $plugId)
    {
        return;// TODO: Implement findByPlugId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        return;// TODO: Implement listEntities() method.
    }

    public function findBySubscriptionId($subscriptionId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);

        $query = "SELECT * FROM $table WHERE subscription_id = $subscriptionId";

        $result = $this->db->fetch($query);
        $repetitions = [];

        if ($result->num_rows === 0) {
            return $repetitions;
        }

        foreach ($result->rows as $row) {
            $repetitionFactory = new RepetitionFactory();
            $repetitions[] = $repetitionFactory->createFromDbData($row);
        }

        return $repetitions;
    }
}
