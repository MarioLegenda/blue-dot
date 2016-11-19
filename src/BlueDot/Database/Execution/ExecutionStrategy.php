<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Exception\CommonInternalException;

class ExecutionStrategy
{
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * @param ArgumentBag $statement
     */
    public function __construct(ArgumentBag $statement)
    {
        $this->statement = $statement;
    }
    /**
     * @return StrategyInterface
     * @throws \BlueDot\Exception\CommonInternalException
     */
    public function getStrategy() : StrategyInterface
    {
        $type = $this->statement->get('type');

        switch($type) {
            case 'simple':
                return new SimpleStrategy($this->statement);
            case 'scenario':
                return new ScenarioStrategy($this->statement);
        }

        throw new CommonInternalException('Internal error. Strategy \''.$type.'\' has not been found. Please, contact whitepostmail@gmail.com');
    }
}