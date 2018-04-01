<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Common\ArgumentBag;
use BlueDot\Kernel\Connection;

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
     * @var ArgumentBag $resultReport
     */
    protected $resultReport;

    public function __construct(ArgumentBag $statement)
    {
        $this->connection = $statement->get('connection');
        $statement->remove('connection');
        $this->statement = $statement;
        $this->resultReport = new ArgumentBag(null, 'result-report');
    }
}