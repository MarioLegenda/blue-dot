<?php

namespace BlueDot;

use BlueDot\Common\{ ArgumentValidator, StatementValidator };

use BlueDot\Configuration\Compiler;

use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\{ Connection, ParameterConversion };

use BlueDot\Database\Execution\{ CallableStrategy, ExecutionContext };

use BlueDot\Entity\Promise;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\{
    BlueDotRuntimeException, ConnectionException, ConfigurationException
};

use BlueDot\StatementBuilder\StatementBuilder;
use Symfony\Component\Yaml\Yaml;

class BlueDot implements BlueDotInterface
{
    /**
     * @var BlueDot $singletonInstance
     */
    private static $singletonInstance;
    /**
     * @var Compiler $compiler
     */
    private $compiler;
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * @param string $configSource
     * @param Connection|null $connection
     * @return BlueDot
     */
    public static function instance(string $configSource = null, Connection $connection = null)
    {
        self::$singletonInstance =
            (self::$singletonInstance instanceof self) ?
                self::$singletonInstance :
                new self($configSource, $connection);

        return self::$singletonInstance;
    }
    /**
     * BlueDot constructor.
     * @param string $configSource
     * @param Connection|null $connection
     * @throws ConfigurationException
     * @throws ConnectionException
     */
    public function __construct(string $configSource = null, Connection $connection = null)
    {
        if (is_null($configSource)) {
            return $this;
        }

        $this->initBlueDot($configSource, $connection);
    }
    /**
     * @param string $name
     * @param null $parameters
     * @return PromiseInterface
     * @throws BlueDotRuntimeException
     * @throws ConnectionException
     */
    public function execute(string $name, $parameters = null) : PromiseInterface
    {
        if (!$this->connection instanceof Connection) {
            throw new ConnectionException(
                sprintf('No connection present. If you constructed BlueDot without configuration, then you have to provide a connection object with %s that accepts an %s object',
                    BlueDotInterface::class,
                    Connection::class
                )
            );
        }

        if (!$this->compiler instanceof Compiler) {
            throw new BlueDotRuntimeException('Configuration does not exist. You have not constructed BlueDot with a configuration file. Only statement builder can be used at this point');
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
    public function setConnection(\PDO $connection) : BlueDotInterface
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
    public function getConnection()
    {
        return $this->connection;
    }
    /**
     * @param string $configSource
     * @return BlueDotInterface
     */
    public function setConfiguration(string $configSource) : BlueDotInterface
    {
        $this->initBlueDot($configSource);

        return $this;
    }
    /**
     * @param Connection|null $connection
     * @return StatementBuilder
     * @throws ConnectionException
     */
    public function createStatementBuilder(Connection $connection = null) : StatementBuilder
    {
        if ($connection instanceof Connection) {
            return new StatementBuilder($connection);
        }

        if (!$this->connection instanceof Connection) {
            throw new ConnectionException('Statement builder connection not established. Either you have to provide a connection with configuration, inject connection with BlueDot::setExternalConnection or pass the '.Connection::class.' object to BlueDot::createStatementBuilder()');
        }

        return new StatementBuilder($this->connection);
    }

    private function initBlueDot($configSource = null, Connection $connection = null)
    {
        $parsedConfiguration = $this->resolveConfiguration($configSource);

        $this->compiler = $this->createCompiler($parsedConfiguration);
        $this->connection = $this->createConnection($parsedConfiguration, $connection);
    }

    private function resolveConfiguration(string $configSource)
    {
        if (!file_exists($configSource)) {
            throw new ConfigurationException('Invalid configuration. Configuration file'.$configSource.'does not exist');
        }

        $parsedConfiguration = Yaml::parse(file_get_contents($configSource));

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
