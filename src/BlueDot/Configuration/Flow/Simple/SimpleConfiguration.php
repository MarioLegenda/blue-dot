<?php

namespace BlueDot\Configuration\Flow\Simple;

use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;

class SimpleConfiguration implements FlowConfigurationProductInterface
{
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var Metadata $metadata
     */
    private $metadata;
    /**
     * @var WorkConfigInterface $workConfig
     */
    private $workConfig;
    /**
     * SimpleConfiguration constructor.
     * @param string $name
     * @param Metadata $metadata
     * @param WorkConfigInterface $workConfig
     */
    public function __construct(
        string $name,
        Metadata $metadata,
        WorkConfigInterface $workConfig
    ) {
        $this->name = $name;
        $this->metadata = $metadata;
        $this->workConfig = $workConfig;
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }
    /**
     * @return WorkConfigInterface
     */
    public function getWorkConfig(): WorkConfigInterface
    {
        return $this->workConfig;
    }
    /**
     * @inheritdoc
     */
    public function injectUserParameters(array $userParameters = null)
    {
        $resolvedUserParameters = null;

        if (!is_null($userParameters) and !empty($userParameters)) {
            $resolvedUserParameters = $userParameters;
        }

        if (is_null($userParameters) and empty($userParameters)) {
            $resolvedUserParameters = [];
        }

        $this->workConfig->injectUserParameters($resolvedUserParameters);
    }
}