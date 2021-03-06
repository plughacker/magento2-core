<?php

namespace PlugHacker\PlugCore\Hub\Repositories;

use PlugHacker\Aggregates\IAggregateRoot;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDatabaseDecorator;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;
use PlugHacker\PlugCore\Hub\Aggregates\InstallToken;
use PlugHacker\PlugCore\Hub\Factories\InstallTokenFactory;

final class InstallTokenRepository extends AbstractRepository
{
    protected function create(AbstractEntity &$object)
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN);

        $stdObject = json_decode(json_encode($object));
        $token = $stdObject->token;
        $used = $stdObject->used ? 'true' : 'false';
        $created_at_timestamp = $stdObject->createdAtTimestamp;
        $expire_at_timestamp = $stdObject->expireAtTimestamp;

        $query = "
             INSERT INTO `$table`" .
            " (token, used, created_at_timestamp, expire_at_timestamp) " .
            " VALUES ('$token',$used,$created_at_timestamp,$expire_at_timestamp)"
          ;

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN);

        $stdObject = json_decode(json_encode($object));
        $token = $stdObject->token;
        $used = $stdObject->used ? 'true' : 'false';
        $created_at_timestamp = $stdObject->createdAtTimestamp;
        $expire_at_timestamp = $stdObject->expireAtTimestamp;

        $query = "
             UPDATE `$table`" .
            " SET " .
            "
                token = '$token' ,
                used = $used ,
                created_at_timestamp = $created_at_timestamp ,
                expire_at_timestamp = $expire_at_timestamp
            " .
            " WHERE id = {$stdObject->id}"
        ;

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByPlugId(AbstractValidString $plugId)
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN);

        $token = $plugId->getValue();

        $query = "SELECT * FROM `$table` as t ";
        $query .= "WHERE t.token = '$token';";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new InstallTokenFactory();
            return $factory->createFromDBData($result->row);
        }

        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN);

        $query = "SELECT * FROM `$table` as t";

        if (!$listDisabled) {
            $query .= " WHERE t.expire_at_timestamp > " . time();
        }

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query . ";");

        $factory = new InstallTokenFactory();
        $installTokens = [];

        foreach ($result->rows as $row) {
            $installToken = $factory->createFromDBData($row);
            $installTokens[] = $installToken;
        }

        return $installTokens;
    }
}
