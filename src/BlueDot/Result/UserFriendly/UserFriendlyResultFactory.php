<?php

namespace BlueDot\Result\UserFriendly;

use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Entity\Entity;
use BlueDot\Kernel\Result\KernelResultInterface;

class UserFriendlyResultFactory
{
    /**
     * @var KernelResultInterface $kernelResult
     */
    private $kernelResult;
    /**
     * UserFriendlyResultFactory constructor.
     * @param KernelResultInterface $kernelResult
     */
    public function __construct(
        KernelResultInterface $kernelResult
    ) {
        $this->kernelResult = $kernelResult;
    }
    /**
     * @return Entity
     */
    public function create(): Entity
    {
        $configuration = $this->kernelResult->getConfiguration();

        if ($configuration instanceof SimpleConfiguration) {
            return SimpleResultFactory::instance()->create($this->kernelResult);
        }

        if ($configuration instanceof ScenarioConfiguration) {
            return ScenarioResultFactory::instance()->create($this->kernelResult);
        }
    }


}