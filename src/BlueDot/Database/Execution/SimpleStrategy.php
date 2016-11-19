<?php

namespace BlueDot\Database\Execution;

class SimpleStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * @return StrategyInterface
     */
    public function execute() : StrategyInterface
    {
        return $this;
    }
}