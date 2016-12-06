<?php

namespace BlueDot\Database;

use BlueDot\Exception\ConnectionException;

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
    public function __construct(array $dsn = array())
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

        if (empty($this->dsn) and !$this->connection instanceof \PDO) {
            throw new ConnectionException('Connection could not be established. Either create the connection with dsn values or set an external connection via \'PDO object by calling Connection::setConnection()');
        }

        $host = $this->dsn['host'];
        $dbName = $this->dsn['database_name'];
        $user = $this->dsn['user'];
        $password = $this->dsn['password'];

        try {
            $this->connection = new \PDO('mysql:host='.$host.';dbname='.$dbName, $user, $password, array(
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ));
        } catch (\PDOException $e) {
            throw new ConnectionException('A PDOException has been thrown when connecting to the database with message \''.$e->getMessage().'\'');
        }

        return $this;
    }
    /**
     * @param \PDO $connection
     */
    public function setConnection(\PDO $connection)
    {
        $this->connection = $connection;
    }
    /**
     * @return \PDO
     */
    public function getConnection() : \PDO
    {
        return $this->connection;
    }
}