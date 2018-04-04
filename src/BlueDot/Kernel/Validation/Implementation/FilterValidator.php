<?php

namespace BlueDot\Kernel\Validation\Implementation;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Common\Util\Util;
use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Simple\Enum\SelectSqlType;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Kernel\Validation\ValidatorInterface;

class FilterValidator implements ValidatorInterface
{
    /**
     * @var FlowConfigurationProductInterface|SimpleConfiguration|ScenarioConfiguration $configuration
     */
    private $configuration;
    /**
     * CorrectSqlValidation constructor.
     * @param FlowConfigurationProductInterface $configuration
     */
    public function __construct(FlowConfigurationProductInterface $configuration)
    {
        $this->configuration = $configuration;
    }
    /**
     * @inheritdoc
     */
    public function validate()
    {
        if ($this->configuration instanceof SimpleConfiguration) {
            $this->validateSimpleConfiguration();
        }

        if ($this->configuration instanceof ScenarioConfiguration) {
            $this->validateScenarioConfiguration();
        }
    }
    /**
     * @throws \RuntimeException
     */
    private function validateSimpleConfiguration()
    {
        $workConfig = $this->configuration->getWorkConfig();
        $metadata = $this->configuration->getMetadata();

        $filter = $workConfig->getFilter();
        /** @var TypeInterface $sqlType */
        $sqlType = $metadata->getSqlType();

        if ($filter instanceof Filter) {
            if (!$sqlType->equals(SelectSqlType::fromValue())) {
                $message = sprintf(
                    'Filters can only be applied to \'select\' sql statements in parent statement \'%s\'',
                    $metadata->getResolvedStatementName()
                );

                throw new \RuntimeException($message);
            }
        }
    }
    /**
     * @throws \RuntimeException
     */
    private function validateScenarioConfiguration()
    {
        /** @var Metadata[] $metadata */
        $metadata = $this->configuration->getMetadata();

        $metadataGenerator = Util::instance()->createGenerator($metadata);

        foreach ($metadataGenerator as $metadata) {
            /** @var Metadata $item */
            $item = $metadata['item'];

            $sqlType = $item->getSqlType();
            $filter = $item->getFilter();

            if ($filter instanceof Filter) {
                if (!$sqlType->equals(SelectSqlType::fromValue())) {
                    $message = sprintf(
                        'Filters can only be applied to \'select\' sql statements in parent statement \'%s\'',
                        $item->getResolvedScenarioStatementName()
                    );

                    throw new \RuntimeException($message);
                }
            }
        }
    }
}