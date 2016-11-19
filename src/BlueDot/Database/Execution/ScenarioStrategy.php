<?php

namespace BlueDot\Database\Execution;

class ScenarioStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * @return StrategyInterface
     */
    public function execute() : StrategyInterface
    {
        return $this;
    }
}