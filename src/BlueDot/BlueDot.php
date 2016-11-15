<?php

namespace BlueDot;

use BlueDot\Configuration\MainConfiguration;
use BlueDot\Database\Scenario\ScenarioStatementExecution;
use BlueDot\Database\ParameterCollectionInterface;
use BlueDot\Database\Simple\SimpleStatementExecution;
use BlueDot\Exception\QueryException;
use BlueDot\Entity\EntityInterface;
use Symfony\Component\Yaml\Yaml;
use BlueDot\Exception\ConfigurationException;
use BlueDot\Cache\Report;

final class BlueDot implements BlueDotInterface
{
    /**
     * @var Report $report
     */
    private $report;
    /**
     * @var object $connection
     */
    private $connection;
    /**
     * @var MainConfiguration $configuration
     */
    private $configuration;
    /**
     * @param $configSource
     * @param mixed $connection
     * @throws ConfigurationException
     */
    public function __construct($configSource, $connection = null)
    {
        $this->report = new Report();

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

        $this->configuration = new MainConfiguration($parsedConfiguration);
    }
    /**
     * @param string $name
     * @param array $parameters
     * @return BlueDotInterface
     */
    public function executeSimple(string $name, $parameters = array())
    {
        $this->establishConnection($this->configuration);

        if (!$parameters instanceof EntityInterface and !is_array($parameters) and !$parameters instanceof ParameterCollectionInterface) {
            throw new QueryException('Invalid argument. If provided, parameters can be an instance of '.EntityInterface::class.', an instance of '.ParameterCollectionInterface::class.' or an array');
        }

        if ($parameters !== null) {
            $speicificConfiguration = $this->configuration->findSimpleByName($name);
            $configParamters = $speicificConfiguration->getParameters();

            $givenParameters = ($parameters instanceof ParameterCollectionInterface) ? $parameters->getBindingKeys() : array_keys($parameters);

            if (!empty(array_diff($configParamters, $givenParameters))) {
                throw new QueryException('Given parameters and parameters in configuration are not equal for '.$speicificConfiguration->getType().'.'.$speicificConfiguration->getName());
            }

            if ($parameters instanceof ParameterCollectionInterface) {
                $parameters = $parameters->toArray();
            } else {
                $parameters = array($parameters);
            }
        }

        $execution = new SimpleStatementExecution(
            'simple',
            $name,
            $this->connection,
            $this->configuration,
            ($parameters instanceof EntityInterface) ? $parameters->toArray() : $parameters,
            $this->report
        );

        return $execution->execute();
    }

    public function executeScenario($name, $parameters = array())
    {
        $this->establishConnection($this->configuration);

        if (!$parameters instanceof EntityInterface and !is_array($parameters) and !$parameters instanceof ParameterCollectionInterface) {
            throw new QueryException('Invalid argument. If provided, parameters can be an instance of '.EntityInterface::class.', an instance of '.ParameterCollectionInterface::class.' or an array');
        }

        $execution = new ScenarioStatementExecution(
            'scenario',
            $name,
            $this->connection,
            $this->configuration,
            $parameters,
            $this->report
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
     * @param MainConfiguration $configuration
     * @return null
     */
    private function establishConnection(MainConfiguration $configuration)
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
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ));
    }
}