<?php

namespace BlueDot\Result\Context;

use BlueDot\Result\NullQueryResult;
use BlueDot\Result\UpdateQueryResult;

class TableContext implements ContextInterface
{
    /**
     * @var \PDOStatement $pdoStatement
     */
    private $pdoStatement;
    /**
     * UpdateContext constructor.
     * @param \PDOStatement $pdoStatement
     */
    public function __construct(\PDOStatement $pdoStatement)
    {
        $this->pdoStatement = $pdoStatement;
    }
    /**
     * @return mixed
     */
    public function makeReport()
    {
        return new UpdateQueryResult($this->pdoStatement->rowCount());
    }
}