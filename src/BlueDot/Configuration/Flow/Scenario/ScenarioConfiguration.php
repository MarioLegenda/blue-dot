<?php

namespace BlueDot\Configuration\Flow\Scenario;

use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;

class ScenarioConfiguration implements FlowConfigurationProductInterface
{
    /**
     * @var RootConfiguration $rootConfiguration
     */
    private $rootConfiguration;
    /**
     * @var Metadata[] $metadata
     */
    private $metadata;
    /**
     * ScenarioConfiguration constructor.
     * @param RootConfiguration $rootConfiguration
     * @param Metadata[] $metadata
     */
    public function __construct(
        RootConfiguration $rootConfiguration,
        array $metadata
    ) {
        $this->rootConfiguration = $rootConfiguration;
        $this->metadata = $metadata;
    }
    /**
     * @return RootConfiguration
     */
    public function getRootConfiguration(): RootConfiguration
    {
        return $this->rootConfiguration;
    }
    /**
     * @return Metadata[]
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}