<?php

namespace BlueDot\Database\Scenario;

use BlueDot\Configuration\Scenario\ScenarioStatementCollection;
use BlueDot\Database\AbstractStatementExecution;

class ScenarioStatementExecution extends AbstractStatementExecution
{
    public function execute()
    {
        $scenarioCollection = new ScenarioStatementCollection($this->specificConfiguration);

        foreach ($scenarioCollection as $scenario) {

        }
    }
}