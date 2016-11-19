<?php

namespace BlueDot\Database\Execution;

interface StrategyInterface
{
    /**
     * @return StrategyInterface
     */
    public function execute() : StrategyInterface;
}