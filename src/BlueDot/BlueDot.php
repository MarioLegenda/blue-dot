<?php

namespace BlueDot;

use BlueDot\Common\{
    ArgumentValidator, FlowProductInterface, StatementValidator
};

use BlueDot\Configuration\Compiler;

use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;

use BlueDot\Entity\Promise;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Connection\ConnectionFactory;

use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\{
    RepositoryException, ConnectionException, ConfigurationException
};

use BlueDot\Kernel\Kernel;
use BlueDot\Kernel\Strategy\PreparedExecution;
use BlueDot\StatementBuilder\StatementBuilder;
use Symfony\Component\Yaml\Yaml;
use BlueDot\Repository\RepositoryInterface;
use BlueDot\Repository\Repository;

class BlueDot implements BlueDotInterface
{
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
     * @throws RepositoryException
     * @throws ConnectionException
     *
     * It is valid to construct BlueDot with empty parameters, but
     * when BlueDot::execute() is called, $configSource and $connection have
     * to be set. This allows the following:
     *
     * - Using BlueDot with multiple databases if we don't specify the database name
     * - Using BlueDot without the repository files (only statement builder)
     * - Making BlueDot light until you start using it. None of the inner mechanisms are
     *   instantiated without the existence of configuration
     */
    public function __construct(
        string $configSource = null
    ) {
        if (is_null($configSource)) {
            return $this;
        }

        if (!file_exists($configSource)) {
            throw new \InvalidArgumentException("The file $configSource does not exist");
        }

        if (!is_readable($configSource)) {
            throw new \InvalidArgumentException("The file $configSource is not readable");
        }

        if (is_file($configSource)) {
            $this->resolveFileSourceInit($configSource);
        }
    }
    /**
     * @param Connection $connection
     * @return BlueDotInterface
     *
     * Set the Connection object for this instance of BlueDot
     *
     * If the connection already exists, it closes the current connection and
     * replaces it with the new one.
     *
     * It is important to know here that even if you create a Connection object,
     * the connection to MySql is still not established. BlueDot establishes the connection
     * just before it knows that he will make a query to the database. So, calling
     * setConnection() does not establishes an actual connection to MySql.
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
     * @throws ConfigurationException
     * @throws ConnectionException
     * @throws RepositoryException
     *
     * Sets the configuration for this instance of BlueDot. This method
     * cannot be called more than once with the same config file.
     */
    public function setConfiguration(string $configSource) : BlueDotInterface
    {
        $this->resolveFileSourceInit($configSource);

        return $this;
    }
    /**
     * @param string $name
     * @param array $parameters
     * @return PromiseInterface
     * @throws ConnectionException
     */
    public function execute(string $name, $parameters = []) : PromiseInterface
    {
        $this->prepareBlueDot();

        /** @var FlowProductInterface $configuration */
        $configuration = $this->compiler->compile($name);

        $kernel = ($configuration instanceof ServiceConfiguration) ?
            new Kernel($configuration, $parameters, $this) :
            new Kernel($configuration, $parameters);

        $kernel->validateKernel();

        $strategy = $kernel->createStrategy($this->connection);

        $kernelResult = $kernel->executeStrategy($strategy);

        $entity = $kernel->convertKernelResultToUserFriendlyResult($kernelResult);

        return new Promise($entity, $entity->getName());
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
     *
     * Gives access to Repository instance
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
     * @throws ConfigurationException
     * @throws ConnectionException
     * @throws RepositoryException
     *
     * Switches BlueDot current configuration and compiles it again.
     *
     * It has the same effect of creating multiple instances of BlueDot with
     * different configuration files.
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
     * @throws ConnectionException
     */
    public function prepareExecution(string $name, $parameters = array()) : BlueDotInterface
    {
        $this->prepareBlueDot();

        /** @var FlowProductInterface $configuration */
        $configuration = $this->compiler->compile($name);

        if (!$this->preparedExecution instanceof PreparedExecution) {
            $this->preparedExecution = new PreparedExecution($this->connection);
        }

        $kernel = new Kernel($configuration, $parameters);

        $this->preparedExecution->addKernel($kernel);

        return $this;
    }
    /**
     * @return array
     * @throws ConnectionException
     * @throws \Exception
     */
    public function executePrepared() : array
    {
        $this->preparedExecution->execute();

        $promises = $this->preparedExecution->getPromises();

        $this->preparedExecution->clear();

        gc_collect_cycles();

        return $promises;
    }

    public function __destruct()
    {
        if ($this->connection instanceof Connection) {
            if ($this->connection->isOpen()) {
                $this->connection->close();
            }

            $this->connection = null;
        }

        gc_collect_cycles();
    }

    /**
     * @param string $configSource
     * @throws ConfigurationException
     * @throws ConnectionException
     *
     * Binds all parts of BlueDot initialization. Check all three private methods
     * for more information
     */
    private function initBlueDot(string $configSource)
    {
        $parsedConfiguration = $this->resolveConfiguration($configSource);

        $this->connection = $this->createConnection($parsedConfiguration);

        $this->compiler = $this->resolveCompiler($configSource, $parsedConfiguration);
    }
    /**
     * @param string $configSource
     * @return array
     * @throws ConfigurationException
     *
     * Parses the yaml file that represent the configuration of BlueDot
     */
    private function resolveConfiguration(string $configSource)
    {
        $parsedConfiguration = Yaml::parse(file_get_contents($configSource));

        if (empty($parsedConfiguration)) {
            throw new ConfigurationException("Invalid configuration. Configuration file $configSource is empty");
        }

        if (!isset($parsedConfiguration['configuration'])) {
            throw new ConfigurationException("Invalid configuration. The 'configuration' key/node does not exist");
        }

        return $parsedConfiguration;
    }
    /**
     * @param string $configSource
     * @param array $parsedConfiguration
     * @return Compiler
     * @throws ConfigurationException
     */
    private function createCompiler(
        string $configSource,
        array $parsedConfiguration
    ) : Compiler {
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
     *
     * Creates the connection from the parsed configuration if the connection
     * key exists in the provided .yml file
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
     * @throws ConnectionException
     */
    private function prepareBlueDot()
    {
        if (is_null($this->repository()->getCurrentlyUsingRepository())) {
            $message = sprintf(
                'There is no currently using repository. If you constructed BlueDot out of a directory which hold repository files, you have to specify which repository you which to use. Use BlueDot::repository()::getWorkingRepositories() to get a list of all available repositories'
            );

            throw new \RuntimeException($message);
        }

        if (!$this->connection instanceof Connection) {
            $message = sprintf(
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
     * @param string $configSource
     * @param array $parsedConfiguration
     * @return Compiler
     * @throws ConfigurationException
     */
    private function resolveCompiler(
        string $configSource,
        array $parsedConfiguration
    ): Compiler {
        return $this->createCompiler($configSource, $parsedConfiguration);
    }
    /**
     * @param string $configSource
     * @throws ConfigurationException
     * @throws ConnectionException
     * @throws RepositoryException
     *
     * Puts the file as the current repository. This method is used by both
     * BlueDot constructor and setConfiguration() method. That method can be
     * called more than once but it is forbidden to init BlueDot with the same
     * configuration more than once. This method converts the exception message
     * thrown from Repository and adds more information to it. Then rethrows it.
     *
     * This is because, Repository implement the RepositoryInterface and therefor,
     * is an independent and decoupled implementation (an independent component) that
     * does not even know that it is implemented by BlueDot.
     */
    private function resolveFileSourceInit(string $configSource)
    {
        try {
            $this->repository()->putRepository($configSource);

            $firstRepository = array_keys($this->repository->getWorkingRepositories())[0];

            $this->useRepository($firstRepository);
        } catch (\RuntimeException $e) {
            $eMessage = $e->getMessage();
            $message = "$eMessage. It is not allowed to initialise BlueDot with the same configuration more than once, usually trough the BlueDot::setConfiguration() method";

            throw new \RuntimeException($message);
        }

    }
}
