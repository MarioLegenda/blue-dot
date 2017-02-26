<?php

namespace BlueDot;

use BlueDot\Command\CreateDatabaseCommand;
use BlueDot\Common\{ ArgumentValidator, StatementValidator, StorageInterface };

use BlueDot\Configuration\Compiler;

use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\{ Connection, ParameterConversion };

use BlueDot\Database\Execution\{ CallableStrategy, ExecutionContext };

use BlueDot\Entity\Promise;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\{
    BlueDotRuntimeException, ConnectionException, ConfigurationException
};

use Symfony\Component\Yaml\Yaml;

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
     * @var Connection $connection
     */
    private $connection;
    /**
     * @param $configSource
     * @param Connection|null $connection
     * @return BlueDot
     */
    public static function instance($configSource = null, Connection $connection = null)
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
    public function __construct($configSource = null, Connection $connection = null)
    {
        if (is_null($configSource)) {
            return $this;
        }

        $this->initBlueDot($configSource, $connection);
    }
    /**
     * @param string $name
     * @param array $parameters
     * @return PromiseInterface
     * @throws BlueDotRuntimeException
     * @throws ConnectionException
     */
    public function execute(string $name, $parameters = array()) : PromiseInterface
    {
        if (!$this->connection instanceof Connection) {
            throw new ConnectionException('No connection present. If you constructed BlueDot without configuration, then you have to provide a Connection object');
        }

        if (!$this->compiler instanceof Compiler) {
            throw new BlueDotRuntimeException('Configuration does not exist. You have not constructed BlueDot with a configuration file. Only statement builder can be used');
        }

        $statement = $this->compiler->compile($name);

        ParameterConversion::instance($parameters, $statement)->convert();

        if ($statement->get('type') === 'callable') {
            $callableStrategy = new CallableStrategy($statement, $this, $parameters);

            $strategy = $callableStrategy->execute();

            return new Promise($strategy->getResult());
        }

        $statement->add('connection', $this->connection);

        $strategy = (new ExecutionContext($statement))->getStrategy();

        return new Promise($strategy->execute()->getResult());
    }
    /**
     * @param \PDO $connection
     * @return BlueDotInterface
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

    private function initBlueDot($configSource = null, Connection $connection = null)
    {
        $parsedConfiguration = $this->resolveConfiguration($configSource);

        $this->compiler = $this->createCompiler($parsedConfiguration);
        $this->connection = $this->createConnection($parsedConfiguration, $connection);
    }

    private function resolveConfiguration($configSource)
    {
        if (!is_string($configSource) and !is_array($configSource)) {
            throw new ConfigurationException('Invalid configuration. Configuration can be a configuration array or a .yml file source');
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

        if (empty($parsedConfiguration)) {
            throw new ConfigurationException('Invalid configuration. Configuration could not be parsed');
        }

        return $parsedConfiguration;
    }

    private function createCompiler(array $parsedConfiguration) : Compiler
    {
        return new Compiler(
            $parsedConfiguration['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($parsedConfiguration)
        );
    }

    private function createConnection(array $parsedConfiguration, Connection $connection = null) : Connection
    {
        if (array_key_exists('connection', $parsedConfiguration['configuration'])) {
            return new Connection($parsedConfiguration['configuration']['connection']);
        }

        if (!$this->connection instanceof Connection) {
            if (!$connection instanceof Connection) {
                throw new ConnectionException('Connection is missing. You can provide connection parameters in the configuration or as a '.Connection::class.' object in the constructor');
            }

            return $connection;
        }
    }
}
