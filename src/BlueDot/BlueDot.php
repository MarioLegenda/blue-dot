<?php

namespace BlueDot;

use BlueDot\Common\{ ArgumentValidator, StatementValidator };

use BlueDot\Configuration\Compiler;

use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\Connection;

use BlueDot\Database\Execution\{ CallableStrategy, ExecutionContext };

use BlueDot\Entity\Promise;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\{
    APIException, BlueDotRuntimeException, ConnectionException, ConfigurationException
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
     * @var API $api
     */
    private $api = array();
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
        if (is_null($configSource) and is_null($connection)) {
            return $this;
        }

        if (!is_null($connection)) {
            if ($this->connection instanceof Connection) {
                $this->connection->close();
            }

            $this->connection = $connection;

            return $this;
        }

        $this->api()->putAPI($configSource);

        $this->initBlueDot($configSource, $connection);
    }
    /**
     * @param string $name
     * @param array|mixed $parameters
     * @param bool $cache
     * @return PromiseInterface
     * @throws BlueDotRuntimeException
     * @throws ConnectionException
     */
    public function execute(string $name, $parameters = array(), bool $cache = true) : PromiseInterface
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

        if ($statement->get('type') === 'callable') {
            if (!is_array($parameters) and !is_null($parameters)) {
                throw new BlueDotRuntimeException(
                    sprintf(
                        'Invalid callable parameter. If provided, parameter for callable has to be an array'
                    )
                );
            }

            $callableStrategy = new CallableStrategy($statement, $this, $parameters);

            $strategy = $callableStrategy->execute();

            return new Promise($strategy->getResult());
        }

        if (!$statement->has('connection')) {
            $statement->add('connection', $this->connection);
        }

        $context = new ExecutionContext($statement, $parameters, $cache);

        return $context->runTasks()->createPromise();
    }
    /**
     * @param Connection $connection
     * @return BlueDotInterface
     */
    public function setConnection(Connection $connection) : BlueDotInterface
    {
        if ($this->connection instanceof Connection) {
            $this->connection->close();
        }

        $this->connection = $connection;

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
    /**
     * @return APIInterface
     */
    public function api() : APIInterface
    {
        if ($this->api instanceof API) {
            return $this->api;
        }

        $this->api = new API();

        return $this->api;
    }
    /**
     * @param string $apiName
     * @return BlueDotInterface
     * @throws APIException
     */
    public function useApi(string $apiName) : BlueDotInterface
    {
        if (!$this->api instanceof API) {
            throw new APIException(
                sprintf(
                    'Invalid API. No API has been created. Create a new api with %s::api() method',
                    BlueDotInterface::class
                )
            );
        }

        $this->initBlueDot($this->api->useAPI($apiName));

        return $this;
    }

    private function initBlueDot($configSource = null, Connection $connection = null)
    {
        $parsedConfiguration = $this->resolveConfiguration($configSource);

        $this->connection = $this->createConnection($parsedConfiguration, $connection);

        $this->compiler = $this->createCompiler($configSource, $parsedConfiguration);
    }

    private function resolveConfiguration(string $configSource)
    {
        if (!file_exists($configSource)) {
            throw new ConfigurationException(
                sprintf(
                    'Invalid configuration. Configuration file %s does not exist',
                    $configSource
                )
            );
        }

        $parsedConfiguration = Yaml::parse(file_get_contents($configSource));

        if (empty($parsedConfiguration)) {
            throw new ConfigurationException('Invalid configuration. Configuration could not be parsed');
        }

        return $parsedConfiguration;
    }

    private function createCompiler(string $configSource, array $parsedConfiguration) : Compiler
    {
        return new Compiler(
            $configSource,
            $parsedConfiguration['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($parsedConfiguration),
            new ImportCollection()
        );
    }

    private function createConnection(array $parsedConfiguration, Connection $connection = null)
    {
        if ($connection instanceof Connection) {
            if ($this->connection instanceof Connection) {
                $this->connection->close();
            }

            return $connection;
        }

        if (array_key_exists('connection', $parsedConfiguration['configuration'])) {
            return new Connection($parsedConfiguration['configuration']['connection']);
        }

        if ($this->connection instanceof Connection) {
            return $this->connection;
        }

        return null;
    }
}
