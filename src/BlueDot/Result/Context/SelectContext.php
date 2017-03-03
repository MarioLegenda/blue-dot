<?php

namespace BlueDot\Result\Context;

use BlueDot\Result\NullQueryResult;
use BlueDot\Result\SelectQueryResult;
use BlueDot\Result\RowMetadata;

class SelectContext implements ContextInterface
{
    /**
     * @var \PDOStatement $pdoStatement
     */
    private $pdoStatement;
    /**
     * SelectContext constructor.
     * @param \PDOStatement $statement
     */
    public function __construct(\PDOStatement $statement)
    {
        $this->pdoStatement = $statement;
    }
    /**
     * @return NullQueryResult|SelectQueryResult
     */
    public function makeReport()
    {
        $queryResult = $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);

        return new SelectQueryResult($queryResult, new RowMetadata($queryResult));
    }
}