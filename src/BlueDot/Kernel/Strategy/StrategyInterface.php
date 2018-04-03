<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Kernel\Result\KernelResultInterface;

interface StrategyInterface
{
    /**
     * @param bool $delayedTransactionCommit
     * @return KernelResultInterface
     */
    public function execute(bool $delayedTransactionCommit): KernelResultInterface;
    /**
     * @param \PDOStatement
     * @return KernelResultInterface
     */
    public function getResult(\PDOStatement $pdoStatement): KernelResultInterface;
}