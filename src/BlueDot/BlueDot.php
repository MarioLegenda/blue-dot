<?php

namespace BlueDot;

use BlueDot\Configuration\Configuration;
use BlueDot\Database\SimpleStatementExecution;
use Symfony\Component\Yaml\Yaml;
use BlueDot\Exception\ConfigurationException;

final class BlueDot implements BlueDotInterface
{
    /**
     * @var object $connection
     */
    private $connection;
    /**
     * @var Configuration $configuration
     */
    private $configuration;
    /**
     * @param $configSource
     * @param mixed $connection
     * @throws ConfigurationException
     */
    public function __construct($configSource, $connection = null)
    {
        if ($connection !== null) {
            $this->connection = $connection;
        }

        $parsedConfiguration = array();
        if (is_array($configSource)) {
            $parsedConfiguration = $configSource;
        } else if (is_string($configSource)) {
            if (!file_exists($configSource)) {
                throw new ConfigurationException('Configuration file'.$configSource.'does not exist');
            }

            $parsedConfiguration = Yaml::parse(file_get_contents($configSource));
        }

        $this->configuration = new Configuration($parsedConfiguration);
    }
    /**
     * @param string $name
     * @param array $parameters
     * @return BlueDotInterface
     */
    public function executeSimple(string $name, array $parameters = array())
    {
        $this->establishConnection($this->configuration);

        $execution = new SimpleStatementExecution(
            $this->connection,
            $this->configuration->findSimpleByName($name),
            $parameters
        );

        return $execution->execute();
    }
    /**
     * @param \PDO $connection
     * @return $this
     */
    public function setExternalConnection(\PDO $connection) : BlueDotInterface
    {
        $this->connection = $connection;

        return $this;
    }
    /**
     * @param Configuration $configuration
     * @return null
     */
    private function establishConnection(Configuration $configuration)
    {
        if ($this->connection instanceof \PDO) {
            return null;
        }

        $dsn = $configuration->getDsn();

        $host = $dsn['host'];
        $dbName = $dsn['database_name'];
        $user = $dsn['user'];
        $password = $dsn['password'];

        $this->connection = new \PDO('mysql:host='.$host.';dbname='.$dbName, $user, $password, array(
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ERRMODE_EXCEPTION => true,
        ));

        $this->connection->exec("set names utf8");
    }
}