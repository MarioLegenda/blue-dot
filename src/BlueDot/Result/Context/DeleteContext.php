<?php

namespace BlueDot\Result\Context;

use BlueDot\Kernel\Connection;
use BlueDot\Result\DeleteQueryResult;
use BlueDot\Result\NullQueryResult;

class DeleteContext implements ContextInterface
{
    /**
     * @var Connection $connection
     */
    private $pdoStatement;
    /**
     * DeleteContext constructor.
     * @param \PDOStatement $pdoStatement
     */
    public function __construct(\PDOStatement $pdoStatement)
    {
        $this->pdoStatement = $pdoStatement;
    }

    public function makeReport()
    {
        $rowCount = $this->pdoStatement->rowCount();

        if (empty($rowCount)) {
            return new NullQueryResult();
        }

        return new DeleteQueryResult($this->pdoStatement->rowCount());
    }
}