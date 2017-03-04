<?php

namespace Test\Components;

use BlueDot\BlueDotInterface;

class ComponentRunner
{
    /**
     * @var TestComponentInterface[] $components
     */
    private $components;
    /**
     * @var \PHPUnit_Framework_Assert $phpunit
     */
    private $phpunit;
    /**
     * @var BlueDotInterface $blueDot
     */
    private $blueDot;
    /**
     * ComponentRunner constructor.
     * @param \PHPUnit_Framework_Assert $phpunit
     * @param BlueDotInterface $blueDot
     */
    public function __construct(\PHPUnit_Framework_Assert $phpunit, BlueDotInterface $blueDot)
    {
        $this->phpunit = $phpunit;
        $this->blueDot = $blueDot;
    }
    /**
     * @param string $componentNamespace
     * @return ComponentRunner
     */
    public function addComponent(string $componentNamespace) : ComponentRunner
    {
        $this->components[] = $componentNamespace;

        return $this;
    }
    /**
     * @void
     */
    public function run()
    {
        foreach ($this->components as $component) {
            $componentObject = new $component($this->phpunit, $this->blueDot);

            $componentObject->run();
        }
    }
}