<?php

namespace BlueDot\Result\UserFriendly;

use BlueDot\Entity\Entity;
use BlueDot\Kernel\Result\KernelResultInterface;

class ScenarioResultFactory
{
    /**
     * @var ScenarioResultFactory $instance
     */
    private static $instance;
    /**
     * @return ScenarioResultFactory
     */
    public static function instance()
    {
        static::$instance = (static::$instance instanceof static) ? static::$instance : new static();

        return static::$instance;
    }
    /**
     * @param KernelResultInterface $kernelResult
     * @return Entity
     */
    public function create(KernelResultInterface $kernelResult): Entity
    {

    }
}