<?php

namespace PlugHacker\PlugCore\Kernel\Abstractions;

abstract class AbstractDatabaseDecorator
{
    const TABLE_MODULE_CONFIGURATION = 0;
    const TABLE_WEBHOOK = 1;
    const TABLE_ORDER = 2;
    const TABLE_CHARGE = 3;
    const TABLE_TRANSACTION = 4;
    const TABLE_HUB_INSTALL_TOKEN = 5;
    const TABLE_SAVED_CARD = 6;
    const TABLE_CUSTOMER = 7;

    protected $db;
    protected $tablePrefix;
    protected $tableArray;
    public function __construct($dbObject)
    {
        $this->db = $dbObject;
        $this->setTablePrefix();
        $this->setTableArray();
    }
    public function query($query)
    {
        $this->doQuery($query);
    }
    public function fetch($query)
    {
        $queryResult = $this->doFetch($query);
        return $this->formatResults($queryResult);
    }
    public function getTable($tableName)
    {
        if (isset($this->tableArray[$tableName])) {
            return $this->tableArray[$tableName];
        }
        throw new \Exception("Table name '$tableName' not found!");
    }
    abstract public function getLastId();
    abstract protected function setTableArray();
    abstract protected function setTablePrefix();
    abstract protected function doQuery($query);

    /**
     *
     * @return array
     */
    abstract protected function doFetch($query);
    abstract protected function formatResults($query);
    abstract protected function setLastInsertId($insertId);
}
