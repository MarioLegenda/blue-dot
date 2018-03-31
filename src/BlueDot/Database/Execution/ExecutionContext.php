<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Database\Execution\Validation\Implementation\CorrectParametersValidation;
use BlueDot\Database\Execution\Validation\Implementation\CorrectSqlValidation;
use BlueDot\Database\Execution\Validation\Implementation\ExistsStatementValidation;
use BlueDot\Database\Execution\Validation\Implementation\ForeignKeyValidation;
use BlueDot\Database\Execution\Validation\Implementation\UseOptionValidation;
use BlueDot\Database\Execution\Validation\ValidationResolver;
use BlueDot\Entity\Entity;
use BlueDot\Entity\Promise;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\BlueDotRuntimeException;

class ExecutionContext
{
    /**
     * @var FlowProductInterface|SimpleConfiguration|ScenarioConfiguration $configuration
     */
    private $configuration;
    /**
     * @var array|null $userParameters
     */
    private $userParameters;
    /**
     * @var StrategyInterface $strategy
     */
    private $strategy;
    /**
     * @var PromiseInterface
     */
    private $promise;
    /**
     * @var Entity $result
     */
    private $result;
    /**
     * @param FlowConfigurationProductInterface|SimpleConfiguration|ScenarioConfiguration $configuration
     * @param array|null $userParameters
     */
    public function __construct(FlowConfigurationProductInterface $configuration, array $userParameters = null)
    {
        $this->configuration = $configuration;
        $this->userParameters = $userParameters;

        $this->configuration->injectUserParameters($userParameters);
    }
    /**
     * @return ExecutionContext
     * @throws \RuntimeException
     */
    public function runTasks() : ExecutionContext
    {
        $this->doRunTasks();

        return $this;
    }
    /**
     * @return ExecutionContext
     * @throws BlueDotRuntimeException
     */
    public function createStrategy() : ExecutionContext
    {
        if (!$this->strategy instanceof StrategyInterface) {
            $this->strategy = $this->doCreateStrategy();
        }

        return $this;
    }
    /**
     * @return StrategyInterface
     * @throws BlueDotRuntimeException
     */
    public function getStrategy() : StrategyInterface
    {
        if (!$this->strategy instanceof StrategyInterface) {
            $this->createStrategy();
        }

        return $this->strategy;
    }
    /**
     * @return ExecutionContext
     * @throws BlueDotRuntimeException
     */
    public function createPromise() : ExecutionContext
    {
        if (is_null($this->result)) {
            throw new BlueDotRuntimeException(
                sprintf(
                    'Invalid execution context. Statement %s has not been executed promise cannot be constructed. This is a bug. Please, contact whitepostmail@gmail.com or post an issue on Github',
                    $this->statement->get('resolved_statement_name')
                )
            );
        }

        if (!$this->promise instanceof PromiseInterface) {
            $this->promise = $this->doCreatePromise();
        }

        return $this;
    }
    /**
     * @return PromiseInterface
     * @throws BlueDotRuntimeException
     */
    public function getPromise() : PromiseInterface
    {
        if (!$this->promise instanceof PromiseInterface) {
            $this->createPromise();
        }

        return $this->promise;
    }
    /**
     * @return ExecutionContext
     * @throws BlueDotRuntimeException
     */
    public function executeStrategy() : ExecutionContext
    {
        $this->createStrategy();

        if (!$this->strategy instanceof StrategyInterface) {
            throw new BlueDotRuntimeException(
                sprintf(
                    'Invalid execution context. Strategy for statement %s has not been constructed. This is a bug. Please, contact whitepostmail@gmail.com or post an issue on Github',
                    $this->statement->get('resolved_statement_name')
                )
            );
        }

        $this->result = $this->strategy->execute()->getResult();

        return $this;
    }
    /**
     * @return PromiseInterface
     * @throws BlueDotRuntimeException
     */
    private function doCreatePromise() : PromiseInterface
    {
        $promise = new Promise($this->result);

        $resolvedStatementName = null;

        if ($this->statement->get('type') === 'scenario') {
            $resolvedStatementName = sprintf('scenario.%s', $this->statement->get('root_config')->get('scenario_name'));
        } else {
            $resolvedStatementName = $this->statement->get('resolved_statement_name');
        }

        $promise->setName($resolvedStatementName);

        return $promise;
    }
    /**
     * @return StrategyInterface
     * @throws BlueDotRuntimeException
     */
    private function doCreateStrategy() : StrategyInterface
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
    /**
     * @throws \RuntimeException
     */
    private function doRunTasks()
    {
        $validatorResolver = new ValidationResolver();

        $validatorResolver
            ->addValidator(new CorrectSqlValidation($this->configuration))
            ->addValidator(new CorrectParametersValidation($this->configuration));

        if ($this->configuration instanceof ScenarioConfiguration) {
            $validatorResolver
                ->addValidator(new ForeignKeyValidation($this->configuration))
                ->addValidator(new UseOptionValidation($this->configuration))
                ->addValidator(new ExistsStatementValidation($this->configuration));
        }

        $validatorResolver->resolveValidation();
/*        $type = $this->statement->get('type');

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
        );*/
    }
}
