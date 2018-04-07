<?php

namespace BlueDot\Configuration\Flow\Service;

use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;

class ServiceConfiguration implements FlowConfigurationProductInterface
{
    /**
     * @var string $resolvedServiceName
     */
    private $resolvedServiceName;
    /**
     * @var string $class
     */
    private $class;
    /**
     * @var $userParameters
     */
    private $userParameters;
    /**
     * ServiceConfiguration constructor.
     * @param string $resolvedServiceName
     * @param string $class
     */
    public function __construct(
        string $resolvedServiceName,
        string $class
    ) {
        $this->resolvedServiceName = $resolvedServiceName;
        $this->class = $class;
    }
    /**
     * @return string
     */
    public function getResolvedServiceName(): string
    {
        return $this->resolvedServiceName;
    }
    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }
    /**
     * @return array
     */
    public function getUserParameters(): array
    {
        return $this->userParameters;
    }
    /**
     * @inheritdoc
     */
    public function injectUserParameters($userParameters = null)
    {
        $resolvedUserParameters = $userParameters;

        if (!is_array($userParameters)) {
            $resolvedUserParameters = [];
        }

        if (empty($userParameters)) {
            $resolvedUserParameters = [];
        }

        $this->userParameters = $resolvedUserParameters;
    }
}