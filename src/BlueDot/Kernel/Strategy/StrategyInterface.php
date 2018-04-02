<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Kernel\Result\KernelResultInterface;

interface StrategyInterface
{
    /**
     * @return KernelResultInterface
     */
    public function execute(): KernelResultInterface;
    /**
     * @param \PDOStatement
     * @return KernelResultInterface
     */
    public function getResult(\PDOStatement $pdoStatement): KernelResultInterface;
}