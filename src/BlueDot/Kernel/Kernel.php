<?php

namespace BlueDot\Kernel;

use BlueDot\BlueDotInterface;
use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Entity\BaseEntity;
use BlueDot\Entity\EntityInterface;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Kernel\Strategy\Enum\ScenarioStrategyType;
use BlueDot\Kernel\Strategy\Enum\ServiceStrategyType;
use BlueDot\Kernel\Strategy\Enum\SimpleStrategyType;
use BlueDot\Kernel\Strategy\ScenarioStrategy;
use BlueDot\Kernel\Strategy\ServiceStrategy;
use BlueDot\Kernel\Strategy\SimpleStrategy;
use BlueDot\Kernel\Strategy\StrategyInterface;
use BlueDot\Kernel\Strategy\StrategyTypeFactory;
use BlueDot\Kernel\Validation\Implementation\BasicCorrectParametersValidation;
use BlueDot\Kernel\Validation\Implementation\CorrectSqlValidation;
use BlueDot\Kernel\Validation\Implementation\ExistsStatementValidation;
use BlueDot\Kernel\Validation\Implementation\FilterValidator;
use BlueDot\Kernel\Validation\Implementation\ForeignKeyValidation;
use BlueDot\Kernel\Validation\Implementation\ModelValidation;
use BlueDot\Kernel\Validation\Implementation\ServiceValidation;
use BlueDot\Kernel\Validation\Implementation\UseOptionValidation;
use BlueDot\Kernel\Validation\ValidationResolver;
use BlueDot\Result\FilterApplier;
use BlueDot\Result\UserFriendly\UserFriendlyResultFactory;

class Kernel
{
    /**
     * @var FlowProductInterface|SimpleConfiguration|ScenarioConfiguration|ServiceConfiguration $configuration
     */
    private $configuration;
    /**
     * @var BlueDotInterface $blueDot
     */
    private $blueDot;
    /**
     * @param FlowConfigurationProductInterface|FlowProductInterface|SimpleConfiguration|ScenarioConfiguration|ServiceConfiguration $configuration
     * @param array|null|object $userParameters
     * @param BlueDotInterface|null $blueDot
     */
    public function __construct(
        FlowConfigurationProductInterface $configuration,
        $userParameters = null,
        BlueDotInterface $blueDot = null
    ) {
        $this->configuration = $configuration;
        $this->blueDot = $blueDot;

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
            ->addValidator(new FilterValidator($this->configuration))
            ->addValidator(new ForeignKeyValidation($this->configuration))
            ->addValidator(new UseOptionValidation($this->configuration))
            ->addValidator(new ExistsStatementValidation($this->configuration))
            ->addValidator(new ServiceValidation($this->configuration));

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

        if ($type->equals(ScenarioStrategyType::fromValue())) {
            return new ScenarioStrategy(
                $this->configuration,
                $connection
            );
        }

        if ($type->equals(ServiceStrategyType::fromValue())) {
            return new ServiceStrategy(
                $this->blueDot,
                $this->configuration,
                $connection
            );
        }
    }
    /**
     * @param StrategyInterface $strategy
     * @param bool $delayedTransactionCommit
     * @return KernelResultInterface
     */
    public function executeStrategy(
        StrategyInterface $strategy,
        bool $delayedTransactionCommit = false
    ): KernelResultInterface {
      $kernelResult = $strategy->execute($delayedTransactionCommit);

      gc_collect_cycles();

      return $kernelResult;
    }
    /**
     * @param KernelResultInterface $kernelResult
     * @return EntityInterface
     */
    public function convertKernelResultToUserFriendlyResult(
        KernelResultInterface $kernelResult
    ): EntityInterface {
        $userFriendlyResultFactory = new UserFriendlyResultFactory(
            $kernelResult,
            new FilterApplier()
        );

        return $userFriendlyResultFactory->create();
    }
}
