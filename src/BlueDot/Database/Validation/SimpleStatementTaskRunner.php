<?php

namespace BlueDot\Database\Validation;

use BlueDot\Common\ArgumentBag;
use BlueDot\Component\ModelConverter;
use BlueDot\Component\TaskRunner\AbstractTaskRunner;
use BlueDot\Component\TaskRunner\TaskInterface;
use BlueDot\Database\Validation\Simple\SimpleParametersResolver;
use BlueDot\Database\Validation\Simple\SimpleStatementParameterValidation;

class SimpleStatementTaskRunner extends AbstractTaskRunner
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
     * @var ModelConverter $modelConverter
     */
    private $modelConverter;
    /**
     * SimpleStatementTaskRunner constructor.
     * @param ArgumentBag $statement
     * @param array|null $parameters
     * @param ModelConverter $modelConverter
     */
    public function __construct(ArgumentBag $statement, $parameters = null, ModelConverter $modelConverter)
    {
        $this->statement = $statement;
        $this->parameters = $parameters;
        $this->modelConverter = $modelConverter;
    }
    /**
     * @param TaskInterface $task
     * @return AbstractTaskRunner
     */
    public function addTask(TaskInterface $task): AbstractTaskRunner
    {
        if ($task instanceof SimpleStatementParameterValidation) {
            $task
                ->addArgument('statement', $this->statement)
                ->addArgument('parameters', $this->parameters);

            $this->tasks[] = $task;
        }

        if ($task instanceof SimpleParametersResolver) {
            $task
                ->addArgument('statement', $this->statement)
                ->addArgument('parameters', $this->parameters)
                ->addArgument('model_converter', $this->modelConverter);

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