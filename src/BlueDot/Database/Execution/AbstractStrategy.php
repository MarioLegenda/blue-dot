<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Database\Connection;
use BlueDot\Database\Parameter\ParameterCollection;

abstract class AbstractStrategy
{
    /**
     * @var Connection $connection
     */
    protected $connection;
    /**
     * @var ArgumentBag $statement
     */
    protected $statement;
    /**
     * @var \PDOStatement $pdoStatement
     */
    protected $pdoStatement;

    public function __construct(ArgumentBag $statement)
    {
        $this->connection = $statement->get('connection');
        $statement->remove('connection');
        $this->statement = $statement;
    }
}