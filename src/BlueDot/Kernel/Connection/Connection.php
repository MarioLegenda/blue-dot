<?php

namespace BlueDot\Kernel\Connection;

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
     * @param array $dsn|null
     * @param Attributes|null $attributes
     * @throws ConnectionException
     *
     * A simple wrapper around \PDO object. Construction this object does not connect
     * to MySql. Only after calling Connection::connect() is BlueDot connected to mysql
     */
    public function __construct(
        array $dsn = null,
        Attributes $attributes = null
    ) {
        if (is_array($dsn)) {
            $this->validateDsn($dsn);

            $this->dsn = $dsn;
            $this->attributes = $attributes;
        }
    }
    /**
     * @param \PDO $pdo
     * @return Connection
     */
    public function setPdo(\PDO $pdo): Connection
    {
        $this->close();

        gc_collect_cycles();

        $this->connection = $pdo;

        return $this;
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
        if ($this->isOpen()) {
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
     * @param array $newDsn
     * @return bool
     */
    public function isSame(array $newDsn): bool
    {
        $diff = array_diff_assoc($newDsn, $this->dsn);

        return empty($diff);
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
                    sprintf('Invalid connection. Missing \'%s\' dsn entry', $entry)
                );
            }

            $dsnEntry = $dsn[$entry];

            if (!is_string($dsnEntry)) {
                throw new ConnectionException(
                    sprintf('Invalid connection. \'%s\' dns entry has to be a string', $entry)
                );
            }
        }
    }
}