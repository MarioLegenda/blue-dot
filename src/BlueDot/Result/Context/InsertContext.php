<?php

namespace BlueDot\Result\Context;

use BlueDot\Database\Connection;
use BlueDot\Result\InsertQueryResult;
use BlueDot\Result\NullQueryResult;

class InsertContext implements ContextInterface
{
    /**
     * @var \PDOStatement $pdoStatement
     */
    private $pdoStatement;
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * InsertContext constructor.
     * @param \PDOStatement $pdoStatement
     * @param Connection $connection
     */
    public function __construct(\PDOStatement $pdoStatement, Connection $connection)
    {
        $this->pdoStatement = $pdoStatement;
        $this->connection = $connection;
    }
    /**
     * @return mixed
     */
    public function makeReport()
    {
        $lastInsertId = $this->connection->getPDO()->lastInsertId();
        $rowCount = $this->pdoStatement->rowCount();

        if (empty($lastInsertId)) {
            return new NullQueryResult();
        }

        return new InsertQueryResult($rowCount, $lastInsertId);
    }
}