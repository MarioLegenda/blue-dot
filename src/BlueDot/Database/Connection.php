<?php

namespace BlueDot\Database;

class Connection
{
    /**
     * @var array $dsn
     */
    private $dsn;
    /**
     * @var \PDO $connection
     */
    private $connection;
    /**
     * @param array $dsn
     */
    public function __construct(array $dsn)
    {
        $this->dsn = $dsn;
    }
    /**
     * @return $this
     */
    public function connect() : Connection
    {
        if ($this->connection instanceof \PDO) {
            return $this;
        }

        $host = $this->dsn['host'];
        $dbName = $this->dsn['database_name'];
        $user = $this->dsn['user'];
        $password = $this->dsn['password'];

        $this->connection = new \PDO('mysql:host='.$host.';dbname='.$dbName, $user, $password, array(
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ));
    }
    /**
     * @return \PDO
     */
    public function getConnection() : \PDO
    {
        return $this->connection;
    }
}