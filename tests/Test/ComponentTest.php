<?php

namespace Test;

use BlueDot\BlueDot;
use Test\Components\ComponentRunner;
use Test\Components\VocalloApi;
use Test\Components\VocalloConnection;
use Test\Components\VocalloDatabase;
use Test\Components\VocalloPreparedExecution;
use Test\Components\VocalloScenario;
use Test\Components\VocalloSeed;
use Test\Components\VocalloSimple;

class ComponentTest extends \PHPUnit_Framework_TestCase
{
    public function testComponents()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/vocallo_user_db.yml');

        $componentRunner = new ComponentRunner($this, $blueDot);

        $componentRunner
            ->addComponent(VocalloDatabase::class, false)
            ->addComponent(VocalloSeed::class, false)
            ->addComponent(VocalloPreparedExecution::class, false)
            ->addComponent(VocalloApi::class, false)
            ->addComponent(VocalloSimple::class, false)
            ->addComponent(VocalloConnection::class, false)
            ->addComponent(VocalloScenario::class);

        $componentRunner->run();
    }
}