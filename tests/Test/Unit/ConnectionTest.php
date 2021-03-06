<?php

namespace Test\Unit;

use BlueDot\BlueDot;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Connection\ConnectionFactory;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends BaseTest
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

        $blueDot = new BlueDot(__DIR__ . '/../config/compiler/connection_test.yml');

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