<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Database\Connection;

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

    public function __construct(ArgumentBag $statement)
    {
        $this->connection = $statement->get('connection');
        $this->statement = $statement;
    }
}