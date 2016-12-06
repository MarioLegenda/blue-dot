<?php

namespace BlueDot;

use BlueDot\Common\{ ArgumentValidator, StatementValidator, StorageInterface };
use BlueDot\Configuration\Compiler;
use BlueDot\Configuration\ConfigurationBuilder;

use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\Connection;

use BlueDot\Database\Execution\{ CallableStrategy, ExecutionContext, StrategyInterface };
use BlueDot\Database\ParameterConversion;

use BlueDot\Exception\ConnectionException;
use Symfony\Component\Yaml\Yaml;
use BlueDot\Exception\ConfigurationException;

class BlueDot implements BlueDotInterface
{
    /**
     * @var Compiler $compiler
     */
    private $compiler;
    /**
     * @var BlueDot $singletonInstance
     */
    private static $singletonInstance;
    /**
     * @var StrategyInterface $strategy
     */
    private $strategy;
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * @var array $configuration
     */
    private $configuration;
    /**
     * @param $configSource
     * @param Connection|null $connection
     * @return BlueDot
     */
    public static function instance($configSource, Connection $connection = null)
    {
        self::$singletonInstance =
            (self::$singletonInstance instanceof self) ?
                self::$singletonInstance :
                new self($configSource, $connection);

        return self::$singletonInstance;
    }
    /**
     * BlueDot constructor.
     * @param $configSource
     * @param Connection|null $connection
     * @throws ConfigurationException
     * @throws ConnectionException
     */
    public function __construct($configSource, Connection $connection = null)
    {
        $parsedConfiguration = array();
        if (is_array($configSource)) {
            $parsedConfiguration = $configSource;
        } else if (is_string($configSource)) {
            if (!file_exists($configSource)) {
                throw new ConfigurationException('Configuration file'.$configSource.'does not exist');
            }

            $parsedConfiguration = Yaml::parse(file_get_contents($configSource));
        }

        $this->compiler = new Compiler($parsedConfiguration['configuration'], new ArgumentValidator(), new StatementValidator());

        if (array_key_exists('connection', $parsedConfiguration['configuration'])) {
            $this->connection = new Connection($parsedConfiguration['configuration']['connection']);
        }

        if (!$this->connection instanceof Connection) {
            if (!$connection instanceof Connection) {
                throw new ConnectionException('Connection is missing. You can provide connection parameters in the configuration or as a '.Connection::class.' object in the constructor');
            }

            $this->connection = $connection;
        }
    }
    /**
     * @param string $name
     * @param array $parameters
     * @return BlueDotInterface
     */
    public function execute(string $name, $parameters = array()) : BlueDotInterface
    {
        $statement = $this->compiler->compile($name);

        ParameterConversion::instance($parameters, $statement)->convert();


        if ($statement->get('type') === 'callable') {
            $callableStrategy = new CallableStrategy($statement, $this, $parameters);

            $this->strategy = $callableStrategy->execute();

            return $this;
        }

        $statement->add('connection', $this->connection);

        $strategy = (new ExecutionContext($statement))->getStrategy();

        $this->strategy = $strategy->execute();

        return $this;
    }
    /**
     * @return StorageInterface
     */
    public function getResult() : StorageInterface
    {
        return $this->strategy->getResult();
    }
    /**
     * @param \PDO $connection
     * @return $this
     */
    public function setExternalConnection(\PDO $connection) : BlueDotInterface
    {
        if (!$this->connection instanceof Connection) {
            $this->connection = new Connection();
            $this->connection->setConnection($connection);

            return $this;
        }

        $this->connection->setConnection($connection);

        return $this;
    }
    /**
     * @return Connection
     */
    public function getConnection() : Connection
    {
        return $this->connection;
    }
}
