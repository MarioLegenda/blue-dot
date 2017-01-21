<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\StorageInterface;

interface StrategyInterface
{
    /**
     * @return StrategyInterface
     */
    public function execute() : StrategyInterface;
    /**
     * @return StorageInterface
     */
    public function getResult();
}