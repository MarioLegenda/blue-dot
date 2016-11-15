<?php

namespace BlueDot\Database;

use BlueDot\Configuration\MainConfiguration;
use BlueDot\Configuration\ConfigurationInterface;
use BlueDot\Cache\Report;

abstract class AbstractStatementExecution
{
    /**
     * @return mixed
     */
    abstract public function execute();
    /**
     * @var \PDO $connection
     */
    protected $connection;
    /**
     * @var ConfigurationInterface $mainConfiguration
     */
    protected $mainConfiguration;
    /**
     * @var ConfigurationInterface $specificConfiguration
     */
    protected $specificConfiguration;
    /**
     * @var array $parameters
     */
    protected $parameters;
    /**
     * @var Report $report
     */
    protected $report;
    /**
     * @param string $type
     * @param string $name
     * @param \PDO $connection
     * @param MainConfiguration $configuration
     * @param mixed $parameters
     * @param Report $report
     */
    public function __construct(string $type, string $name, \PDO $connection, MainConfiguration $configuration, $parameters = null, Report $report)
    {
        $this->connection = $connection;
        $this->mainConfiguration = $configuration;
        $this->specificConfiguration = $configuration->findByType($type, $name);
        $this->parameters = $parameters;
        $this->report = $report;
    }
}