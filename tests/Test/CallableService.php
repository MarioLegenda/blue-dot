<?php

namespace Test;

use BlueDot\BlueDotInterface;
use BlueDot\Common\CallableInterface;
use BlueDot\Common\StorageInterface;
use BlueDot\Entity\Entity;

class CallableService implements CallableInterface
{
    /**
     * @var BlueDotInterface $blueDot
     */
    private $blueDot;
    /**
     * @var array $parameters
     */
    private $parameters;
    /**
     * @param BlueDotInterface $blueDot
     * @param array $parameters
     */
    public function __construct(BlueDotInterface $blueDot, array $parameters = array())
    {
        $this->blueDot = $blueDot;
        $this->parameters = $parameters;
    }
    /**
     * @void
     */
    public function run() : StorageInterface
    {
        return new Entity();
    }
}