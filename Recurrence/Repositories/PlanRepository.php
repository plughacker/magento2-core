<?php

namespace PlugHacker\PlugCore\Recurrence\Repositories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDatabaseDecorator;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;
use PlugHacker\PlugCore\Recurrence\Aggregates\Plan;
use PlugHacker\PlugCore\Recurrence\Factories\PlanFactory;

final class PlanRepository extends AbstractRepository
{
    /** @param Plan $object */
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );

        $query = "
          INSERT INTO $table
            (
                interval_type,
                interval_count,
                name,
                description,
                plan_id,
                product_id,
                credit_card,
                installments,
                boleto,
                billing_type,
                status,
                trial_period_days
            )
          VALUES
            (
                '{$object->getIntervalType()}',
                '{$object->getIntervalCount()}',
                '{$object->getName()}',
                '{$object->getDescription()}',
                '{$object->getPlugId()->getValue()}',
                '{$object->getProductId()}',
                '{$object->getCreditCard()}',
                '{$object->getAllowInstallments()}',
                '{$object->getBoleto()}',
                '{$object->getBillingType()}',
                '{$object->getStatus()}',
                '{$object->getTrialPeriodDays()}'
            )
        ";

        $this->db->query($query);
        $object->setId($this->db->getLastId());

        $this->saveSubProducts($object);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );

        $query = "
            UPDATE $table SET
                `interval_type` = '{$object->getIntervalType()}',
                `interval_count` = '{$object->getIntervalCount()}',
                `name` = '{$object->getName()}',
                `description` = '{$object->getDescription()}',
                `plan_id` = '{$object->getPlugId()->getValue()}',
                `product_id` = '{$object->getProductId()}',
                `credit_card` = '{$object->getCreditCard()}',
                `installments` = '{$object->getAllowInstallments()}',
                `boleto` = '{$object->getBoleto()}',
                `billing_type` = '{$object->getBillingType()}',
                `status` = '{$object->getStatus()}',
                `trial_period_days` = '{$object->getTrialPeriodDays()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);

        $this->saveSubProducts($object);
    }

    /** @param Plan $object */
    public function find($objectId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );
        $query = "SELECT * FROM $table WHERE id = '$objectId' LIMIT 1";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        return $this->genericFind($result->row);
    }

    public function listEntities($limit, $listDisabled)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );

        $query = "SELECT * FROM {$table}";

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $listPlan = [];
        foreach ($result->rows as $row) {
            $listPlan[] = $this->genericFind($row);
        }

        return $listPlan;
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );

        $query = "DELETE FROM $table WHERE id = {$object->getId()}";

        $result = $this->db->query($query);

        $this->deleteSubproducts($object);

        return $result;
    }
    public function findByPlugId(AbstractValidString $plugId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );
        $objectId = $plugId->getValue();

        $query = "SELECT * FROM $table WHERE plan_id = '{$objectId}' LIMIT 1";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        return $this->genericFind($result->row);
    }

    public function deleteSubproducts(AbstractEntity &$object)
    {
        $subProductRepository = new SubProductRepository();
        foreach ($object->getItems() as $subProduct) {
            $subProductRepository->delete($subProduct);
        }
    }

    public function saveSubProducts(AbstractEntity &$object)
    {
        $subProductRepository = new SubProductRepository();
        foreach ($object->getItems() as $subProduct) {
            $subProduct->setProductRecurrenceId($object->getId());
            $subProduct->setRecurrenceType($object->getRecurrenceType());
            $subProductRepository->save($subProduct);
        }
    }

    public function findByProductId($productId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );
        $query = "SELECT * FROM $table WHERE product_id = '{$productId}' LIMIT 1";

        $result = $this->db->fetch($query);

        if ($result->num_rows == 0) {
            return null;
        }
        return $this->genericFind($result->row);
    }

    private function genericFind($row)
    {
        $factory = new PlanFactory();
        $plan = $factory->createFromDbData($row);

        $subProductsRepository = new SubProductRepository();
        $subProducts = $subProductsRepository->findByRecurrence($plan);

        $plan->setItems($subProducts);

        return $plan;
    }
}
