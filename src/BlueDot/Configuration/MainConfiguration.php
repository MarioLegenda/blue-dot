<?php

namespace BlueDot\Configuration;

use BlueDot\Configuration\Scenario\ScenarioStatementCollection;
use BlueDot\Database\Scenario\ScenarioStatementExecution;
use BlueDot\StatementFactory;
use BlueDot\Exception\ConfigurationException;

class MainConfiguration
{
    /**
     * @var array $connectionType
     */
    private $dsn = array();
    /**
     * @var array $simples
     */
    private $simples = array();
    /**
     * @var array $scenarios
     */
    private $scenarios = array();
    /**
     * @var array $foundStatements
     */
    private $foundStatements = array();
    /**
     * @constructor
     * @param array $configuration
     * @throws ConfigurationException
     */
    public function __construct(array $configuration)
    {
        if (!array_key_exists('configuration', $configuration)) {
            throw new ConfigurationException('Invalid configuration file. Top element should be \'configuration\'');
        }

        $configuration = $configuration['configuration'];

        if (array_key_exists('connection', $configuration)) {
            $connection = $configuration['connection'];

            $validKeys = array('host', 'database_name', 'user', 'password');

            foreach ($validKeys as $key) {
                if (!array_key_exists($key, $connection)) {
                    throw new ConfigurationException('Invalid connection configuration. Missing '.$key.' configuration value');
                }
            }

            $this->dsn = $connection;
        }

        if (array_key_exists('simple', $configuration)) {
            $this->simples = StatementFactory::createSimpleStatements($configuration['simple']);
        }

        if (array_key_exists('scenario', $configuration)) {
            $this->scenarios = StatementFactory::createScenarioStatements($configuration['scenario']);
        }
    }

    public function findByType(string $type, string $name)
    {
        if ($type !== 'simple' and $type !== 'scenario' and $type !== 'custom') {
            throw new ConfigurationException('Invalid configuration. Unknown option of name '.$type);
        }

        if ($type === 'simple') {
            $simple = $this->findSimpleByName($name);

            if ($simple === null) {
                throw new ConfigurationException('Unknown statement simple.'.$name);
            }

            return $simple;
        }

        if ($type === 'scenario') {
            $scenario = $this->findScenarioByName($name);

            if ($scenario === null) {
                throw new ConfigurationException('Unknown statement scenario.'.$name);
            }

            return $scenario;
        }

        return null;
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function findSimpleByName(string $name)
    {
        if (array_key_exists($name, $this->foundStatements)) {
            return $this->foundStatements[$name];
        }

        $exploded = explode('.', $name);

        if (count($exploded) === 1) {
            throw new ConfigurationException('Invalid \'simple\' statement call. The correct notation is simple.some_statement. '.$name.' given');
        }

        if (count($exploded) === 2) {
            $statementType = $exploded[0];
            $statementName = $exploded[1];

            foreach ($this->simples as $simple) {
                if ($simple->getType() === $statementType) {
                    if ($simple->getName() === $statementName) {
                        $this->foundStatements[$name] = $simple;
                        return $simple;
                    }
                }
            }

            throw new ConfigurationException('Query with name '.$name.' has not been found in the configuration under '.$statementType.' statement type. This could be an internal error so please contact the creator of this tool at whitepostmail@gmail.com');
        }

        throw new ConfigurationException('Query with name '.$name.' has not been found in the configuration. This could be an internal error so please contact the creator of this tool at whitepostmail@gmail.com');
    }

    /**
     * @param string $name
     * @return mixed
     * @throws ConfigurationException
     */
    public function findScenarioByName(string $name)
    {
        if (array_key_exists($name, $this->foundStatements)) {
            return $this->foundStatements[$name];
        }

        foreach ($this->scenarios as $key => $scenario) {
            if ($key === $name) {
                return $this->scenarios->getScenario($name);
            }
        }

        throw new ConfigurationException('Scenario '.$name.' has not been found under \'scenario\' configuration entry');
    }
    /**
     * @return string
     */
    public function getDsn() : array
    {
        return $this->dsn;
    }
}