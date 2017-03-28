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
use BlueDot\Entity\ModelConverter as EntityModelConterter;
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
     * @var bool $cache
     */
    private $cache;
    /**
     * @var Entity $result
     */
    private $result;
    /**
     * @param ArgumentBag $statement
     * @param bool $cache
     * @param mixed $parameters
     */
    public function __construct(ArgumentBag $statement, $parameters = null, bool $cache)
    {
        $this->statement = $statement;
        $this->parameters = $parameters;
        $this->cache = $cache;
    }
    /**
     * @return ExecutionContext
     */
    public function runTasks() : ExecutionContext
    {
        $this->doRunTasks();

        if ($this->statement->has('cache') and $this->statement->get('cache') === true and $this->cache === true) {
            if (CacheStorage::getInstance()->canBeCached($this->statement)) {
                $cache = CacheStorage::getInstance();
                $name = $cache->createName($this->statement);

                if ($cache->has($name)) {
                    $result = $cache->get($name);

                    if ($this->statement->has('model')) {
                        $modelConverter = new EntityModelConterter($this->statement->get('model'), $result[0]);

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
        } else if ($this->statement->has('cache') and $this->statement->get('cache') === false) {
            if (CacheStorage::getInstance()->canBeCached($this->statement)) {
                $cache = CacheStorage::getInstance();
                $name = $cache->createName($this->statement);

                if ($cache->has($name)) {
                    $cache->remove($name);
                }
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
                return new SimpleStrategy($this->statement);
            case 'scenario':
                return new ScenarioStrategy($this->statement);
        }

        throw new BlueDotRuntimeException('Internal error. Strategy \''.$type.'\' has not been found. Please, contact whitepostmail@gmail.com or post an issue');
    }

    private function doRunTasks()
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

                return;
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

                return;
        }

        throw new BlueDotRuntimeException(
            sprintf(
                'Internal error. Strategy %s has not been found. This is probably a bug. Please, contact whitepostmail@gmail.com or post an issue on Github',
                $type
            )
        );
    }
}
