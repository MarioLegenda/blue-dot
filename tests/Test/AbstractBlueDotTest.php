<?php

namespace Test;

use BlueDot\BlueDot;
use BlueDot\BlueDotInterface;
use BlueDot\Database\Connection;

class AbstractBlueDotTest extends \PHPUnit_Framework_TestCase
{
    public function testBase()
    {
        $this->configurationConnectionTest();
        $this->bareConnectionTest();
    }

    private function configurationConnectionTest()
    {
        $blueDot = $this->createEmptyBlueDot();

        $blueDot->setConfiguration(__DIR__.'/config/connection_configuration.yml');

        $this->assertInstanceOf(Connection::class, $blueDot->getConnection());

        $connection = $blueDot->getConnection();

        $connection->connect();

        $this->assertInstanceOf(\PDO::class, $connection->getPDO());

        $connection->close();
    }

    private function bareConnectionTest()
    {
        $connection = new Connection();
        $connection
            ->setHost('127.0.0.1')
            ->setUser('root')
            ->setPassword('root')
            ->setDatabaseName('');

        $blueDot = new BlueDot(null, $connection);

        $this->assertInstanceOf(Connection::class, $blueDot->getConnection());

        $connection->connect();

        $this->assertInstanceOf(\PDO::class, $connection->getPDO());

        $connection->close();
    }

    private function singletonTest()
    {
        $blueDot = BlueDot::instance(__DIR__.'/config/connection_configuration.yml');

        $this->assertInstanceOf(BlueDot::class, $blueDot);

        $blueDotSecond = BlueDot::instance(__DIR__.'/config/connection_configuration.yml');

        $this->assertSame($blueDotSecond, $blueDot);

        $blueDot->getConnection()->close();
    }

    private function createEmptyBlueDot() : BlueDotInterface
    {
        return new BlueDot();
    }
}