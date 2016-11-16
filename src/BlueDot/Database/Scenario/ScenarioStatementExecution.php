<?php

namespace BlueDot\Database\Scenario;

use BlueDot\Configuration\Scenario\ScenarioStatementCollection;
use BlueDot\Database\AbstractStatementExecution;

class ScenarioStatementExecution extends AbstractStatementExecution
{
    public function execute()
    {
        $specificConfiguration = $this->argumentsBag->get('specific_configuration');

        $scenarioCollection = new ScenarioStatementCollection($specificConfiguration);

        foreach ($scenarioCollection as $scenario) {
            if ($scenario->hasUseOption()) {
                $useOption = $scenario->getUseOption();


            }
        }
    }
}