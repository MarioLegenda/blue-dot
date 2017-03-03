<?php

namespace BlueDot\Result\Context;

use BlueDot\Result\NullQueryResult;
use BlueDot\Result\UpdateQueryResult;

class UpdateContext implements ContextInterface
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
        $rowCount = $this->pdoStatement->rowCount();

        if (empty($rowCount)) {
            return new NullQueryResult();
        }

        return new UpdateQueryResult($this->pdoStatement->rowCount());
    }
}