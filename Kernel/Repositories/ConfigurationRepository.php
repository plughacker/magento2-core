<?php

namespace PlugHacker\PlugCore\Kernel\Repositories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDatabaseDecorator;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractRepository;
use PlugHacker\PlugCore\Kernel\Factories\ConfigurationFactory;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class ConfigurationRepository extends AbstractRepository
{
    private $pattern = '/\\\s+\\\s\\\r\\\n|\\\r|\\\n\\\r|\\\n/m';

    /**
     * @param string $jsonEncoded
     * @return string
     */
    private function removeSpecialCharacters($jsonEncoded)
    {
        return trim(
            preg_replace(
                $this->pattern,
                ' ',
                $jsonEncoded
            )
        );
    }

    protected function create(AbstractEntity &$object)
    {
        $jsonEncoded = json_encode($object);

        $jsonEncoded = $this->removeSpecialCharacters($jsonEncoded);
        $preparedObject = json_decode($jsonEncoded);
        $preparedObject->parent = null;
        $jsonEncoded = json_encode($preparedObject);

        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);

        $query = "INSERT INTO `$configTable` (data, store_id) VALUES ('$jsonEncoded', {$object->getStoreId()})";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);
        $query = " SELECT * FROM `$configTable` WHERE id = {$object->getId()};";

        $result = $this->db->fetch($query);

        if ($result->num_rows == 0) {
            return $this->create($object);
        }

        $jsonEncoded = json_encode($object);
        $jsonEncoded = $this->removeSpecialCharacters($jsonEncoded);

        $preparedObject = json_decode($jsonEncoded);
        $preparedObject->parent = null;
        $jsonEncoded = json_encode($preparedObject);

        $query = "
            UPDATE `$configTable` set data = '{$jsonEncoded}'
            WHERE id = {$object->getId()};
        ";

        return $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);

        $query = "SELECT data, id FROM `$configTable` WHERE id = {$objectId};";

        $result = $this->db->fetch($query);

        $factory = new ConfigurationFactory();

        if (empty($result->row)) {
            return null;
        }

        $config =  $factory->createFromJsonData($result->row['data']);
        $config->setId($result->row['id']);

        return $config;
    }

    public function findByStore($storeId)
    {
        if ($storeId === null) {
            return null;
        }

        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);

        $query = "SELECT data, id FROM `$configTable` WHERE store_id = {$storeId};";

        $result = $this->db->fetch($query);

        $factory = new ConfigurationFactory();

        if (empty($result->row)) {
            return null;
        }

        $config = $factory->createFromJsonData($result->row['data']);
        $config->setId($result->row['id']);

        return $config;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByPlugId(AbstractValidString $plugId)
    {
        // TODO: Implement findByPlugId() method.
    }
}
