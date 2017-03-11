<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Database\Validation\Scenario\ScenarioParametersResolver;
use BlueDot\Database\Validation\Scenario\ScenarioStatementParametersValidation;
use BlueDot\Database\Validation\ScenarioStatementTaskRunner;
use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Component\TaskRunner\TaskRunnerFactory;
use BlueDot\Database\Validation\SimpleStatementTaskRunner;
use BlueDot\Component\ModelConverter;
use BlueDot\Database\Validation\Simple\SimpleStatementParameterValidation;
use BlueDot\Database\Validation\Simple\SimpleParametersResolver;

class ExecutionContext
{
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * @var mixed $parameters
     */
    private $parameters;
    /**
     * @param ArgumentBag $statement
     * @param mixed $parameters
     */
    public function __construct(ArgumentBag $statement, $parameters = null)
    {
        $this->statement = $statement;
        $this->parameters = $parameters;
    }
    /**
     * @return StrategyInterface
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function getStrategy() : StrategyInterface
    {
        $type = $this->statement->get('type');

        switch($type) {
            case 'simple':
                TaskRunnerFactory::createTaskRunner(function() {
                    return new SimpleStatementTaskRunner(
                        $this->statement,
                        $this->parameters,
                        new ModelConverter());
                })
                    ->addTask(new SimpleStatementParameterValidation())
                    ->addTask(new SimpleParametersResolver())
                    ->doTasks();

                return new SimpleStrategy($this->statement);
            case 'scenario':
                TaskRunnerFactory::createTaskRunner(function() {
                    return new ScenarioStatementTaskRunner(
                        $this->statement,
                        $this->parameters,
                        new ModelConverter()
                    );
                })
                    ->addTask(new ScenarioStatementParametersValidation())
                    ->addTask(new ScenarioParametersResolver())
                    ->doTasks();

                return new ScenarioStrategy($this->statement);
        }

        throw new BlueDotRuntimeException('Internal error. Strategy \''.$type.'\' has not been found. Please, contact whitepostmail@gmail.com or post an issue');
    }
}