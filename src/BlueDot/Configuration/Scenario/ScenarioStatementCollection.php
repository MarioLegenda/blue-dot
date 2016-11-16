<?php

namespace BlueDot\Configuration\Scenario;

use BlueDot\Configuration\ConfigurationInterface;
use BlueDot\Exception\ConfigurationException;

class ScenarioStatementCollection implements \IteratorAggregate
{
    /**
     * @var array $scenarioStatements
     */
    private $scenarioStatements = array();

    public function __construct(array $scenarioStatements = array())
    {
        if (!empty($scenarioStatements)) {
            foreach ($scenarioStatements as $scenario) {
                if (!$scenario instanceof ConfigurationInterface) {
                    throw new ConfigurationException('Invalid argument in '.ScenarioStatementCollection::class.'. Expected an array of '.ScenarioStatement::class);
                }
            }

            $this->scenarioStatements = $scenarioStatements;
        }
    }
    /**
     * @param ScenarioStatement $scenarioStatement
     * @return ScenarioStatementCollection
     */
    public function add(string $name, ScenarioStatement $scenarioStatement) : ScenarioStatementCollection
    {
        $this->scenarioStatements[$name][$scenarioStatement->getName()] = $scenarioStatement;

        return $this;
    }
    /**
     * @param string $scenarioName
     * @param string $executionName
     * @return bool
     */
    public function hasScenarioStatement(string $scenarioName, string $executionName) : bool
    {
        if (!array_key_exists($scenarioName, $this->scenarioStatements)) {
            return false;
        }

        foreach ($this->scenarioStatements[$scenarioName] as $scenarioStatement) {
            if ($scenarioStatement->getName() === $executionName) {
                return true;
            }
        }

        return false;
    }

    public function getScenarioStatement(string $scenarioName, string $executionName)
    {
        if ($this->hasScenario($scenarioName, $executionName)) {
            return $this->scenarioStatements[$scenarioName][$executionName];
        }
    }
    /**
     * @param string $scenarioName
     * @return bool
     */
    public function hasScenario(string $scenarioName) : bool
    {
        return array_key_exists($scenarioName, $this->scenarioStatements);
    }
    /**
     * @param string $scenarioName
     * @return mixed
     */
    public function getScenario(string $scenarioName)
    {
        if ($this->hasScenario($scenarioName)) {
            return $this->scenarioStatements[$scenarioName];
        }

        return null;
    }
    /**
     * @param string $scenarioName
     * @return array
     */
    public function getScenarioStatementNames(string $scenarioName) : array
    {
        return array_keys($this->getScenario($scenarioName));
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->scenarioStatements);
    }
}