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
     * @var array $attributes
     */
    private $attributes = array();
    /**
     * Connection constructor.
     * @param array $dsn
     * @param Attributes $attributes
     * @throws ConnectionException
     */
    public function __construct(array $dsn, Attributes $attributes)
    {
        $this->validateDsn($dsn);

        $this->dsn = $dsn;
        $this->attributes = $attributes;
    }
    /**
     * @return Attributes
     */
    public function getAttributes(): Attributes
    {
        return $this->attributes;
    }
    /**
     * @return Connection
     * @throws ConnectionException
     */
    public function connect(): Connection
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
            $this->connection = new \PDO('mysql:host='.$host.';dbname='.$dbName, $user, $password, $this->attributes->toArray());
        } catch (\PDOException $e) {
            throw new ConnectionException('A PDOException has been thrown when connecting to the database with message \''.$e->getMessage().'\'');
        }

        return $this;
    }
    /**
     * @return Connection
     */
    public function close(): Connection
    {
        $this->connection = null;

        return $this;
    }
    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return !$this->connection instanceof \PDO;
    }
    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->connection instanceof \PDO;
    }
    /**
     * @return \PDO|null
     */
    public function getPDO(): ?\PDO
    {
        return $this->connection;
    }
    /**
     * @param array $dsn
     * @throws ConnectionException
     */
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
    }
}