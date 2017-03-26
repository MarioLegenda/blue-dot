<?php

namespace BlueDot\Database\Execution;

use BlueDot\Cache\CacheStorage;
use BlueDot\Common\ArgumentBag;
use BlueDot\Database\Validation\Scenario\ScenarioParametersResolver;
use BlueDot\Database\Validation\Scenario\ScenarioStatementParametersValidation;
use BlueDot\Database\Validation\ScenarioStatementTaskRunner;
use BlueDot\Entity\Entity;
use BlueDot\Entity\Promise;
use BlueDot\Entity\PromiseInterface;
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
     * @var Entity $result
     */
    private $result;
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
     * @return ExecutionContext
     */
    public function runTasks() : ExecutionContext
    {
        if ($this->statement->has('resolved_statement_name')) {
          $cache = CacheStorage::getInstance();

          if ($cache->has($this->statement)) {
              $result = $cache->get($this->statement);

              if ($this->statement->has('model')) {
                  $modelConverter = new ModelConverter($this->statement->get('model'), $result);

                  $this->result = $modelConverter->convertIntoModel();

                  if (is_array($this->result)) {
                      $this->result = new Entity($this->result);
                  }

                  return $this;
              } else {
                  $this->result = new Entity($result);
              }

              return $this;
          }
        }

        $strategy = $this->createStrategy();

        $this->result = $strategy->execute()->getResult();

        return $this;
    }
    /**
     * @return PromiseInterface
     */
    public function createPromise() : PromiseInterface
    {
        return new Promise($this->result);
    }

    private function createStrategy() : StrategyInterface
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
                        $this->parameters
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
