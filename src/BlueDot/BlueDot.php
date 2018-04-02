<?php

namespace BlueDot;

use BlueDot\Common\{
    ArgumentValidator, FlowProductInterface, StatementValidator
};

use BlueDot\Configuration\Compiler;

use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;

use BlueDot\Entity\Promise;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Connection\ConnectionFactory;

use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\{
    RepositoryException, BlueDotRuntimeException, ConnectionException, ConfigurationException
};

use BlueDot\Kernel\Kernel;
use BlueDot\StatementBuilder\StatementBuilder;
use Symfony\Component\Yaml\Yaml;
use BlueDot\Repository\RepositoryInterface;
use BlueDot\Repository\Repository;

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
     * @var RepositoryInterface $repository
     */
    private $repository = array();
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
            $this->repository()->putRepository($configSource);

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
     */
    public function execute(string $name, $parameters = array(), bool $cache = true) : PromiseInterface
    {
        $this->prepareBlueDot();

        /** @var FlowProductInterface $configuration */
        $configuration = $this->compiler->compile($name);

        $kernel = new Kernel($configuration, $parameters);

        $kernel->validateKernel();

        $strategy = $kernel->createStrategy($this->connection);

        $kernelResult = $kernel->executeStrategy($strategy);

        $entity = $kernel->convertKernelResultToUserFriendlyResult($kernelResult);

        return new Promise($entity);
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
     * @return RepositoryInterface
     */
    public function repository() : RepositoryInterface
    {
        if ($this->repository instanceof RepositoryInterface) {
            return $this->repository;
        }

        $this->repository = new Repository();

        return $this->repository;
    }
    /**
     * @param string $repository
     * @return BlueDotInterface
     * @throws RepositoryException
     * @throws ConfigurationException
     * @throws ConnectionException
     */
    public function useRepository(string $repository) : BlueDotInterface
    {
        if (!$this->repository instanceof Repository) {
            throw new RepositoryException(
                sprintf(
                    'Invalid repository. No repository has been created. Create a new repository with %s::repository() method',
                    BlueDotInterface::class
                )
            );
        }

        $this->initBlueDot($this->repository->useRepository($repository));

        return $this;
    }
    /**
     * @param string $name
     * @param array $parameters
     * @return BlueDotInterface
     * @throws BlueDotRuntimeException
     * @throws ConnectionException
     */
    public function prepareExecution(string $name, $parameters = array()) : BlueDotInterface
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

        $executionContext = new Kernel($statement, $parameters);

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
     * @throws BlueDotRuntimeException
     * @throws ConfigurationException
     * @throws ConnectionException
     * @throws Exception\CompileException
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
     * @throws BlueDotRuntimeException
     * @throws ConfigurationException
     * @throws Exception\CompileException
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
            $message =                 sprintf(
                'No connection present. If you constructed BlueDot without configuration, then you have to provide a connection object with \'%s\' that accepts an \'%s\' object',
                BlueDotInterface::class,
                Connection::class
            );

            throw new ConnectionException($message);
        }

        if (!$this->compiler instanceof Compiler) {
            throw new \RuntimeException('Configuration does not exist. You have not constructed BlueDot with a configuration file. Only statement builder can be used at this point');
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
