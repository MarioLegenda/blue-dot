<?php

namespace BlueDot;

use BlueDot\Common\ArgumentBag;
use BlueDot\Configuration\MainConfiguration;
use BlueDot\Database\Scenario\ScenarioBuilder;
use BlueDot\Database\StatementExecution;
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

        $scenarioBuilder = new ScenarioBuilder((new ArgumentBag())
            ->add('type', 'simple')
            ->add('user_parameters', $parameters)
            ->add('connection', $this->connection)
            ->add('configuration', $this->configuration->findByType('simple', $name))
        );

        $scenario = $scenarioBuilder->buildScenario();

        $statementExecution = new StatementExecution($scenario);

        return $statementExecution->execute()->getResult();
    }

    public function executeScenario($name, $parameters = array())
    {
        $this->establishConnection($this->configuration);

        $scenarioBuilder = new ScenarioBuilder((new ArgumentBag())
            ->add('type', 'scenario')
            ->add('user_parameters', $parameters)
            ->add('connection', $this->connection)
            ->add('configuration', $this->configuration->findByType('scenario', $name))
            ->add('report', $this->report)
        );

        $scenario = $scenarioBuilder->buildScenario();

        $statementExecution = new StatementExecution($scenario);

        return $statementExecution->execute()->getResult();
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
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ));
    }
}