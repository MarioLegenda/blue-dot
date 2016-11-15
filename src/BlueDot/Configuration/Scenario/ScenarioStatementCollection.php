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
        $this->scenarioStatements[$name][] = $scenarioStatement;

        return $this;
    }
    /**
     * @param string $name
     * @param string $scenarioName
     * @return bool
     */
    public function hasScenario(string $name, string $scenarioName) : bool
    {
        if (!array_key_exists($name, $this->scenarioStatements)) {
            return false;
        }

        foreach ($this->scenarioStatements[$name] as $scenarioStatement) {
            if ($scenarioStatement->getName() === $scenarioName) {
                return true;
            }
        }

        return false;
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->scenarioStatements);
    }
}