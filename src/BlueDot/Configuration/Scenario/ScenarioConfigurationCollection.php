<?php

namespace BlueDot\Configuration\Scenario;

use BlueDot\Common\AbstractArgumentBag;

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
}