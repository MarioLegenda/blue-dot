<?php

namespace BlueDot\Database;

use BlueDot\Common\StorageInterface;
use BlueDot\Database\Scenario\Scenario;
use BlueDot\Database\Scenario\ScenarioStatementExecution;
use BlueDot\Database\Simple\SimpleStatementExecution;

class StatementExecutionFactory
{
    public static function createExecutionStatement(Scenario $scenario) : AbstractStatementExecution
    {
        if ($scenario->getArgumentBag()->get('type') === 'simple') {
            return new SimpleStatementExecution($scenario);
        }

        if ($scenario->getArgumentBag()->get('type') === 'scenario') {
            return new ScenarioStatementExecution($scenario);
        }
    }
}