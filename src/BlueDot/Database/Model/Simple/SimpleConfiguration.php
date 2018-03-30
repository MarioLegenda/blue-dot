<?php

namespace BlueDot\Database\Model\Simple;

use BlueDot\Configuration\Flow\FlowProductInterface;
use BlueDot\Database\Model\ConfigurationInterface;
use BlueDot\Database\Model\MetadataInterface;
use BlueDot\Database\Model\WorkConfigInterface;

class SimpleConfiguration implements ConfigurationInterface, FlowProductInterface
{
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var MetadataInterface $metadata
     */
    private $metadata;
    /**
     * @var WorkConfigInterface $workConfig
     */
    private $workConfig;
    /**
     * SimpleConfiguration constructor.
     * @param string $name
     * @param MetadataInterface $metadata
     * @param WorkConfigInterface $workConfig
     */
    public function __construct(
        string $name,
        MetadataInterface $metadata,
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
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface
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