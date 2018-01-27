<?php

namespace BlueDot\Database;

class ConnectionFactory
{
    /**
     * @param array $dsn
     * @param array $attributes
     * @return Connection
     * @throws \BlueDot\Exception\ConnectionException
     */
    public static function createConnection(
        array $dsn,
        array $attributes = []
    ): Connection {
        if (empty($attributes)) {
            $attributes = array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::ATTR_PERSISTENT => true,
            );

            $attributes = new Attributes($attributes);
        }

        return new Connection($dsn, $attributes);
    }
}