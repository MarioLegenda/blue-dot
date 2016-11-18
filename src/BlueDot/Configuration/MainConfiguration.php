<?php

namespace BlueDot\Configuration;

use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Configuration\Validator\Validator;
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
        $validator = new ConfigurationValidator($configuration);

        $validator->validate();
    }

    public function findByType(string $type, string $name)
    {
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function findSimpleByName(string $name)
    {

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

        if ($this->scenarios->has($name)) {
            return $this->scenarios->get($name);
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