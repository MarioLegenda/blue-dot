<?php

namespace BlueDot\Database\Validation;

use BlueDot\Component\TaskRunner\AbstractTaskRunner;
use BlueDot\Component\TaskRunner\TaskInterface;
use BlueDot\Common\ArgumentBag;
use BlueDot\Component\ModelConverter;
use BlueDot\Database\Validation\Scenario\ScenarioParametersResolver;
use BlueDot\Database\Validation\Scenario\ScenarioStatementParametersValidation;

class ScenarioStatementTaskRunner extends AbstractTaskRunner
{
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * @var array $parameters
     */
    private $parameters;
    /**
     * SimpleStatementTaskRunner constructor.
     * @param ArgumentBag $statement
     * @param array|null $parameters
     */
    public function __construct(ArgumentBag $statement, $parameters = null)
    {
        $this->statement = $statement;
        $this->parameters = $parameters;
    }

    public function addTask(TaskInterface $task): AbstractTaskRunner
    {
        if ($task instanceof ScenarioStatementParametersValidation) {
            $task
                ->addArgument('statement', $this->statement)
                ->addArgument('parameters', $this->parameters);

            $this->tasks[] = $task;
        }

        if ($task instanceof ScenarioParametersResolver) {
            $task
                ->addArgument('statement', $this->statement)
                ->addArgument('parameters', $this->parameters);

            $this->tasks[] = $task;
        }

        return $this;
    }

    public function doTasks()
    {
        $previousOptions = array();
        foreach ($this->tasks as $task) {
            if (!empty($previousOptions)) {
                $task->setOptions($previousOptions);
            }

            $task->doTask();

            $previousOptions = $task->getOptions();
        }
    }
}