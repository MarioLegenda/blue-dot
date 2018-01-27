<?php

namespace Test;

use BlueDot\BlueDot;
use BlueDot\Database\Connection;
use BlueDot\Database\ConnectionFactory;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function test_connection()
    {
        $connection = $this->createConnection();

        static::assertInstanceOf(Connection::class, $connection);

        static::assertTrue($connection->isClosed());
        static::assertFalse($connection->isOpen());

        $connection->connect();

        static::assertInstanceOf(\PDO::class, $connection->getPDO());

        $connection->close();

        static::assertNull($connection->getPDO());
    }

    public function test_connection_within_blue_dot()
    {
        $blueDot = new BlueDot();

        $blueDot->setConnection($this->createConnection());

        static::assertInstanceOf(Connection::class, $blueDot->getConnection());

        $connection = $blueDot->getConnection();

        $connection->close();

        static::assertTrue($connection->isClosed());
        static::assertFalse($connection->isOpen());

        $blueDot = new BlueDot(__DIR__.'/config/connection_test.yml');

        $connection = $blueDot->getConnection();

        static::assertInstanceOf(Connection::class, $blueDot->getConnection());

        static::assertTrue($connection->isClosed());
        static::assertFalse($connection->isOpen());
    }
    /**
     * @return Connection
     * @throws \BlueDot\Exception\ConnectionException
     */
    private function createConnection(): Connection
    {
        return ConnectionFactory::createConnection([
            'host' => '127.0.0.1',
            'database_name' => '',
            'user' => 'root',
            'password' => 'root',
        ]);
    }
}