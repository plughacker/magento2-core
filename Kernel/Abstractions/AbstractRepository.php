<?php

namespace PlugHacker\PlugCore\Kernel\Abstractions;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

abstract class AbstractRepository
{
    /**
     *
     * @var AbstractDatabaseDecorator
     */
    protected $db;

    /**
     * AbstractRepository constructor.
     */
    public function __construct()
    {
        $this->db = MPSetup::getDatabaseAccessDecorator();
    }

    public function save(AbstractEntity &$object)
    {
        $objectId = null;
        if (is_object($object)
            && method_exists($object, 'getId')
        ) {
            $objectId = $object->getId();
        }
        if ($objectId === null) {
            $createResult = $this->create($object);
            if (!$object->getId()) {
                $object->setId($this->db->getLastId());
            }
            return $createResult;
        }

        return $this->update($object);
    }

    abstract protected function create(AbstractEntity &$object);
    abstract protected function update(AbstractEntity &$object);
    abstract public function delete(AbstractEntity $object);
    abstract public function find($objectId);
    abstract public function findByPlugId(AbstractValidString $plugId);
    abstract public function listEntities($limit, $listDisabled);
}
