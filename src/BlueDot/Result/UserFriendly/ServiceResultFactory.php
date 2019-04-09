<?php

namespace BlueDot\Result\UserFriendly;

use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Entity\BaseEntity;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Result\FilterApplier;

class ServiceResultFactory
{
    /**
     * @var ScenarioResultFactory $instance
     */
    private static $instance;
    /**
     * @return ScenarioResultFactory
     */
    public static function instance()
    {
        static::$instance = (static::$instance instanceof static) ? static::$instance : new static();

        return static::$instance;
    }
    /**
     * @param KernelResultInterface $kernelResult
     * @param FilterApplier $filterApplier
     * @return BaseEntity
     */
    public function create(
        KernelResultInterface $kernelResult,
        FilterApplier $filterApplier
    ): BaseEntity {
        /** @var ServiceConfiguration $configuration */
        $configuration = $kernelResult->getConfiguration();

        $result = [
            'sql_type' => null,
            'row_count' => null,
            'data' => $kernelResult->getResult(),
        ];

        return new BaseEntity(
            $result,
            $configuration->getResolvedServiceName()
        );
    }
}