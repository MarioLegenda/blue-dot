<?php

namespace BlueDot\Configuration\Scenario;

use BlueDot\Common\AbstractArgumentBag;
use BlueDot\Common\StorageInterface;

class ScenarioConfigurationCollection extends AbstractArgumentBag
{
    /**
     * @param string $scenarioConfigName
     * @param string $scenarioStatementName
     * @return bool
     * @throws \BlueDot\Exception\CommonInternalException
     */
    public function hasScenarioConfiguration(string $scenarioStatementName) : bool
    {
        return $this->has($scenarioStatementName);
    }
    /**
     * @param string $scenarioStatementName
     * @return mixed|null
     * @throws \BlueDot\Exception\CommonInternalException
     */
    public function getScenarioConfiguration(string $scenarioStatementName) : ScenarioConfiguration
    {
        if (!$this->has($scenarioStatementName)) {
            return null;
        }

        return $this->get($scenarioStatementName);
    }
}