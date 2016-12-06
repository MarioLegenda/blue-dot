<?php

namespace BlueDot\Configuration\Scenario;

use BlueDot\Common\AbstractArgumentBag;

class ScenarioConfigurationCollection extends AbstractArgumentBag
{
    /**
     * @param string $scenarioConfigName
     * @param string $scenarioStatementName
     * @return bool
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function hasScenarioConfiguration(string $scenarioStatementName) : bool
    {
        return $this->has($scenarioStatementName);
    }
    /**
     * @param string $scenarioStatementName
     * @return mixed|null
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function getScenarioConfiguration(string $scenarioStatementName) : ScenarioConfiguration
    {
        if (!$this->has($scenarioStatementName)) {
            return null;
        }

        return $this->get($scenarioStatementName);
    }

    public function findConfigurationInUseOption()
    {
        $useOptionConfigurations = new ScenarioConfigurationCollection();
        foreach ($this->arguments as $argument) {
            if ($argument->has('use_option')) {
                $useOption = $argument->get('use_option');
                $scenarioConfiguration = $this->getScenarioConfiguration($useOption->getName());

                $useOptionConfigurations->add($useOption->getName(), $scenarioConfiguration);
            }
        }

        return $useOptionConfigurations;
    }
}