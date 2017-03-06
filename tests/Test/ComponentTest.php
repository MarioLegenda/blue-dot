<?php

namespace Test;

use BlueDot\BlueDot;
use Test\Components\ComponentRunner;
use Test\Components\VocalloScenario;
use Test\Components\VocalloSimple;

class ComponentTest extends \PHPUnit_Framework_TestCase
{
    public function testComponents()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/vocallo_user_db.yml');

        $componentRunner = new ComponentRunner($this, $blueDot);

        $componentRunner
            ->addComponent(VocalloSimple::class, false)
            ->addComponent(VocalloScenario::class);

        $componentRunner->run();
    }
}