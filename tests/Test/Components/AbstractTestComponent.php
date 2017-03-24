<?php

namespace Test\Components;

use BlueDot\BlueDotInterface;

abstract class AbstractTestComponent
{
    /**
     * @var $phpunit
     */
    protected $phpunit;
    /**
     * @var BlueDotInterface $blueDot
     */
    protected $blueDot;
    /**
     * VocalloScenario constructor.
     * @param \PHPUnit_Framework_Assert $phpunit
     * @param BlueDotInterface $blueDot
     */
    public function __construct(\PHPUnit_Framework_Assert $phpunit, BlueDotInterface $blueDot)
    {
        $this->phpunit = $phpunit;
        $this->blueDot = $blueDot;
    }
}