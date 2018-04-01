<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Common\StorageInterface;

interface StrategyInterface
{
    /**
     * @void
     */
    public function execute();
    /**
     * @return StorageInterface
     */
    public function getResult();
}