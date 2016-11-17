<?php

namespace BlueDot\Database\Scenario;


use BlueDot\Exception\QueryException;

class ScenarioCollection implements \IteratorAggregate
{
    /**
     * @var array $scenarious
     */
    private $scenarious = array();
    /**
     * @param string $name
     * @param Scenario $scenario
     * @return $this
     */
    public function addScenario(string $name, Scenario $scenario) : ScenarioCollection
    {
        $this->scenarious[$name] = $scenario;

        return $this;
    }

    public function hasScenario(string $name) : bool
    {
        return array_key_exists($name, $this->scenarious);
    }

    public function getScenario(string $name) : Scenario
    {
        if (!$this->hasScenario($name)) {
            throw new QueryException('Scenario '.$name.' not found');
        }

        return $this->scenarious[$name];
    }

    public function isUsedAsOption(string $name) : bool
    {
        if (!$this->hasScenario($name)) {
            throw new QueryException('Scenario '.$name.' has not been found but it should be at this point in ScenarioCollection::isUsedAsOption()');
        }

        foreach ($this->scenarious as $scenario) {
            $config = $scenario->getArgumentBag()->get('specific_configuration');

            if ($config->hasUseOption()) {
                $useOption = $config->getUseOption();

                if ($useOption->getName() === $name) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->scenarious);
    }
}