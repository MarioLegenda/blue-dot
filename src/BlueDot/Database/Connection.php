<?php

namespace BlueDot\Database;

use BlueDot\Exception\ConnectionException;

class Connection
{
    /**
     * @var array $dsn
     */
    private $dsn = array();
    /**
     * @var \PDO $connection
     */
    private $connection;
    /**
     * @param array $dsn
     */
    public function __construct(array $dsn = array())
    {
        if (!empty($dsn)) {
            $this->validateDsn($dsn);

            $this->dsn = $dsn;
        }
    }
    /**
     * @param string $host
     * @return Connection
     */
    public function setHost(string $host) : Connection
    {
        $this->dsn['host'] = $host;

        return $this;
    }
    /**
     * @param string $databaseName
     * @return Connection
     */
    public function setDatabaseName(string $databaseName) : Connection
    {
        $this->dsn['database_name'] = $databaseName;

        return $this;
    }

    /**
     * @param string $user
     * @return Connection
     */
    public function setUser(string $user) : Connection
    {
        $this->dsn['user'] = $user;

        return $this;
    }
    /**
     * @param string $password
     * @return Connection
     */
    public function setPassword(string $password) : Connection
    {
        $this->dsn['password'] = $password;

        return $this;
    }
    /**
     * @return Connection
     * @throws ConnectionException
     */
    public function connect() : Connection
    {
        if ($this->connection instanceof \PDO) {
            return $this;
        }

        $this->validateDsn($this->dsn);

        if (empty($this->dsn) and !$this->connection instanceof \PDO) {
            throw new ConnectionException('Connection could not be established. Either create the connection with dsn values or set an external connection via \'PDO object by calling Connection::setConnection()');
        }

        $host = $this->dsn['host'];
        $dbName = $this->dsn['database_name'];
        $user = $this->dsn['user'];
        $password = $this->dsn['password'];

        try {
            $options = array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            );

            $options[\PDO::ATTR_PERSISTENT] = true;

            if (array_key_exists('persistent', $this->dsn)) {
                $options[\PDO::ATTR_PERSISTENT] = $this->dsn['persistent'];
            }

            $this->connection = new \PDO('mysql:host='.$host.';dbname='.$dbName, $user, $password, $options);
        } catch (\PDOException $e) {
            throw new ConnectionException('A PDOException has been thrown when connecting to the database with message \''.$e->getMessage().'\'');
        }

        return $this;
    }
    /**
     * @return Connection
     */
    public function close() : Connection
    {
        $this->connection = null;

        return $this;
    }
    /**
     * @param \PDO $connection
     */
    public function setPDO(\PDO $connection)
    {
        $this->connection = $connection;
    }
    /**
     * @return \PDO
     */
    public function getPDO() : \PDO
    {
        return $this->connection;
    }

    private function validateDsn(array $dsn)
    {
        $valids = array('host', 'database_name', 'user', 'password');

        foreach ($valids as $entry) {
            if (array_key_exists($entry, $dsn) === false) {
                throw new ConnectionException(
                    sprintf('Invalid connection. Missing \'%s\' dsn entry', $key)
                );
            }

            $dsnEntry = $dsn[$entry];

            if (!is_string($dsnEntry)) {
                throw new ConnectionException(
                    sprintf('Invalid connection. \'%s\' dns entry has to be a string', $key)
                );
            }
        }

        if (array_key_exists('persistent', $dsn)) {
            if (!is_bool($dsn['persistent'])) {
                throw new ConnectionException('Invalid connection. \'persistent\' dsn option has to be a boolean');
            }
        }
    }
}