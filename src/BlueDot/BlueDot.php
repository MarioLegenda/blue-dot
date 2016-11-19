<?php

namespace BlueDot;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\BlueDotConfiguration;
use BlueDot\Configuration\ConfigurationBuilder;
use BlueDot\Configuration\MainConfiguration;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\Connection;
use BlueDot\Database\Scenario\ScenarioBuilder;
use BlueDot\Database\StatementExecution;
use BlueDot\Entity\Entity;
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
     * @var Connection $connection
     */
    private $connection;
    /**
     * @var array $configuration
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

        $configBuilder = new ConfigurationBuilder(new ConfigurationValidator($parsedConfiguration));

        $this->configuration =
            $configBuilder
                ->buildConfiguration()
                ->getConfiguration();
    }
    /**
     * @param string $name
     * @param array $parameters
     * @return Entity
     */
    public function execute(string $name, $parameters = array()) : Entity
    {
        $statementValidator = new StatementValidator(new ArgumentValidator($name), $this->configuration);

        $statement = $statementValidator->validate()->getStatement();

        return new Entity();
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
}