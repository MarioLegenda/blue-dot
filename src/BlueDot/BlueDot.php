<?php

namespace BlueDot;

use BlueDot\Common\{ ArgumentValidator, StatementValidator };

use BlueDot\Configuration\Compiler;

use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\Connection;

use BlueDot\Database\ConnectionFactory;
use BlueDot\Database\Execution\{
    CallableStrategy, ExecutionContext, PreparedExecution
};

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
     * @var string $configSource
     */
    private $configSource;
    /**
     * @var Compiler $compiler
     */
    private $compiler;
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * @var PreparedExecution $preparedExecution
     */
    private $preparedExecution;
    /**
     * @var API $api
     */
    private $api = array();

    /**
     * BlueDot constructor.
     * @param string|null $configSource
     * @throws ConfigurationException
     * @throws ConnectionException
     *
     * It is valid to construct BlueDot with empty parameters, but
     * when BlueDot::execute() is called, $configSource and $connection have
     * to be set. This allows querying multiple databases with one instance
     * of BlueDot
     */
    public function __construct(string $configSource = null)
    {
        if (is_null($configSource)) {
            return $this;
        }

        if (is_file($configSource)) {
            $this->api()->putAPI($configSource);

            $this->initBlueDot($configSource);
        }
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
     * @return Connection|null
     */
    public function getConnection(): ?Connection
    {
        return $this->connection;
    }
    /**
     * @param string $configSource
     * @return BlueDotInterface
     */
    public function setConfiguration(string $configSource) : BlueDotInterface
    {
        $this->configSource = $configSource;

        return $this;
    }
    /**
     * @param string $name
     * @param array $parameters
     * @param bool $cache
     * @return PromiseInterface
     * @throws BlueDotRuntimeException
     * @throws ConnectionException
     * @throws Exception\CompileException
     */
    public function execute(string $name, $parameters = array(), bool $cache = true) : PromiseInterface
    {
        $this->prepareBlueDot();

        $statement = $this->compiler->compile($name);

        if ($statement->get('type') === 'callable') {
            if (!is_array($parameters) and !is_null($parameters)) {
                throw new BlueDotRuntimeException(
                    sprintf(
                        'Invalid callable parameter. If provided, parameter for callable has to be an array'
                    )
                );
            }

            $statement = $statement->get($name);

            $callableStrategy = new CallableStrategy($statement, $this, $parameters);

            $strategy = $callableStrategy->execute();

            $promise = new Promise($strategy->getResult());

            $promise->setName($statement->get('resolved_statement_name'));

            return $promise;
        }

        if (!$statement->has('connection')) {
            $statement->add('connection', $this->connection);
        }

        $context = new ExecutionContext($statement, $parameters, $cache);

        return $context
            ->runTasks()
            ->executeStrategy()
            ->getPromise();
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
     * @throws ConfigurationException
     * @throws ConnectionException
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
    /**
     * @param string $name
     * @param array $parameters
     * @param bool $cache
     * @return BlueDotInterface
     * @throws BlueDotRuntimeException
     * @throws ConnectionException
     * @throws Exception\CompileException
     */
    public function prepareExecution(string $name, $parameters = array(), bool $cache = true) : BlueDotInterface
    {
        $this->prepareBlueDot();

        $statement = $this->compiler->compile($name);

        if ($statement->get('type') !== 'simple') {
            throw new BlueDotRuntimeException(
                sprintf(
                    'Invalid prepared execution statement \'%s\'. Only simple statements can be prepared for execution',
                    $statement->get('resolved_statement_name')
                )
            );
        }

        if (!$this->preparedExecution instanceof PreparedExecution) {
            $this->preparedExecution = $this->createPreparedExecution();
        }

        if (!$statement->has('connection')) {
            $statement->add('connection', $this->connection);
        }

        $executionContext = new ExecutionContext($statement, $parameters, $cache);

        $executionContext->runTasks();

        $this->preparedExecution->addStrategy($name, $executionContext->getStrategy());

        return $this;
    }
    /**
     * @return array
     * @throws ConnectionException
     */
    public function executePrepared() : array
    {
        $promises = $this->preparedExecution->execute()->getPromises();

        $this->preparedExecution->clear();

        return $promises;
    }
    /**
     * @param string $configSource
     * @throws ConfigurationException
     * @throws ConnectionException
     */
    private function initBlueDot(string $configSource)
    {
        $parsedConfiguration = $this->resolveConfiguration($configSource);

        $this->connection = $this->createConnection($parsedConfiguration);

        $this->compiler = $this->createCompiler($configSource, $parsedConfiguration);
    }
    /**
     * @param string $configSource
     * @return array
     * @throws ConfigurationException
     */
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
    /**
     * @param string $configSource
     * @param array $parsedConfiguration
     * @return Compiler
     * @throws ConfigurationException
     */
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
    /**
     * @param array $parsedConfiguration
     * @return Connection|null
     * @throws ConnectionException
     */
    private function createConnection(array $parsedConfiguration)
    {
        if (array_key_exists('connection', $parsedConfiguration['configuration'])) {
            $connectionArray = $parsedConfiguration['configuration']['connection'];

            if ($this->connection instanceof Connection) {
                $this->connection->close();
            }

            return ConnectionFactory::createConnection($connectionArray);
        }

        if ($this->connection instanceof Connection) {
            return $this->connection;
        }

        return null;
    }
    /**
     * @throws BlueDotRuntimeException
     * @throws ConnectionException
     */
    private function prepareBlueDot()
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

        if (!$this->connection->isOpen()) {
            $this->connection->connect();
        }
    }
    /**
     * @return PreparedExecution
     */
    private function createPreparedExecution() : PreparedExecution
    {
        return new PreparedExecution($this->connection);
    }
}
