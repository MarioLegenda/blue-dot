<?php

namespace BlueDot\Result\UserFriendly;

use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Entity\Entity;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Result\FilterApplier;

class UserFriendlyResultFactory
{
    /**
     * @var KernelResultInterface $kernelResult
     */
    private $kernelResult;
    /**
     * @var FilterApplier $filterApplier
     */
    private $filterApplier;
    /**
     * UserFriendlyResultFactory constructor.
     * @param KernelResultInterface $kernelResult
     * @param FilterApplier $filterApplier
     */
    public function __construct(
        KernelResultInterface $kernelResult,
        FilterApplier $filterApplier
    ) {
        $this->kernelResult = $kernelResult;
        $this->filterApplier = $filterApplier;
    }
    /**
     * @return Entity
     */
    public function create(): Entity
    {
        $configuration = $this->kernelResult->getConfiguration();

        if ($configuration instanceof SimpleConfiguration) {
            return SimpleResultFactory::instance()->create(
                $this->kernelResult,
                $this->filterApplier
            );
        }

        if ($configuration instanceof ScenarioConfiguration) {
            return ScenarioResultFactory::instance()->create(
                $this->kernelResult,
                $this->filterApplier
            );
        }

        if ($configuration instanceof ServiceConfiguration) {
            return ServiceResultFactory::instance()->create(
                $this->kernelResult,
                $this->filterApplier
            );
        }
    }
}