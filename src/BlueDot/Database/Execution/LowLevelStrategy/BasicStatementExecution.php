<?php

namespace BlueDot\Database\Execution\LowLevelStrategy;

use BlueDot\Common\ArgumentBag;

class BasicStatementExecution
{
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * BasicStatementExecution constructor.
     * @param ArgumentBag $statement
     */
    public function __construct(ArgumentBag $statement)
    {
        $this->statement = $statement;
    }


}