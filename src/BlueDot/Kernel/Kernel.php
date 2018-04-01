<?php

namespace BlueDot\Kernel;

use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Strategy\Enum\SimpleStrategyType;
use BlueDot\Kernel\Strategy\SimpleStrategy;
use BlueDot\Kernel\Strategy\StrategyInterface;
use BlueDot\Kernel\Strategy\StrategyTypeFactory;
use BlueDot\Kernel\Validation\Implementation\BasicCorrectParametersValidation;
use BlueDot\Kernel\Validation\Implementation\CorrectSqlValidation;
use BlueDot\Kernel\Validation\Implementation\ExistsStatementValidation;
use BlueDot\Kernel\Validation\Implementation\ForeignKeyValidation;
use BlueDot\Kernel\Validation\Implementation\ModelValidation;
use BlueDot\Kernel\Validation\Implementation\UseOptionValidation;
use BlueDot\Kernel\Validation\ValidationResolver;
use BlueDot\Entity\Entity;
use BlueDot\Entity\Promise;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\BlueDotRuntimeException;

class Kernel
{
    /**
     * @var FlowProductInterface|SimpleConfiguration|ScenarioConfiguration $configuration
     */
    private $configuration;
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
     * @param array|null|object $userParameters
     */
    public function __construct(
        FlowConfigurationProductInterface $configuration,
        $userParameters = null
    ) {
        $this->configuration = $configuration;

        $this->configuration->injectUserParameters($userParameters);
    }
    /**
     * @return Kernel
     * @throws \RuntimeException
     */
    public function validateKernel() : Kernel
    {
        $validatorResolver = new ValidationResolver();

        $validatorResolver
            ->addValidator(new CorrectSqlValidation($this->configuration))
            ->addValidator(new BasicCorrectParametersValidation($this->configuration))
            ->addValidator(new ModelValidation($this->configuration))
            ->addValidator(new ForeignKeyValidation($this->configuration))
            ->addValidator(new UseOptionValidation($this->configuration))
            ->addValidator(new ExistsStatementValidation($this->configuration));

        $validatorResolver->resolveValidation();

        return $this;
    }
    /**
     * @param Connection $connection
     * @return StrategyInterface
     */
    public function createStrategy(Connection $connection) : StrategyInterface
    {
        $type = StrategyTypeFactory::getType($this->configuration);

        if ($type->equals(SimpleStrategyType::fromValue())) {
            return new SimpleStrategy(
                $this->configuration,
                $connection
            );
        }
    }
    /**
     * @return Kernel
     * @throws BlueDotRuntimeException
     */
    public function createPromise() : Kernel
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
     * @return Kernel
     * @throws BlueDotRuntimeException
     */
    public function executeStrategy() : Kernel
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

    }
}
