<?php

namespace BlueDot\Database\Execution\Validation\Implementation;

use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Scenario\UseOption;
use BlueDot\Database\Execution\Validation\ValidatorInterface;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;

class UseOptionValidation implements ValidatorInterface
{
    /**
     * @var ScenarioConfiguration $configuration
     */
    private $configuration;
    /**
     * UseOptionValidation constructor.
     * @param ScenarioConfiguration $scenarioConfiguration
     */
    public function __construct(
        ScenarioConfiguration $scenarioConfiguration
    ) {
        $this->configuration = $scenarioConfiguration;
    }
    /**
     * @inheritdoc
     */
    public function validate()
    {
        /** @var Metadata[] $metadata */
        $metadata = $this->configuration->getMetadata();

        $this->validateUseOptions($metadata);
    }
    /**
     * @param array $metadata
     * @throws \RuntimeException
     */
    private function validateUseOptions(array $metadata)
    {
        /** @var Metadata $item */
        foreach ($metadata as $item) {
            /** @var UseOption $useOption */
            $useOption = $item->getUseOption();
            $parentResolvedScenarioName = $this->configuration->getRootConfiguration()->getScenarioName();

            if ($useOption instanceof UseOption) {
                $useOptionStatementName = $useOption->getStatementName();

                if (!array_key_exists($useOptionStatementName, $metadata)) {
                    $message = sprintf(
                        'Invalid \'use\' option statement name. Statement \'%s\' does not exist in scenario \'%s\' for parent statement \'%s\'',
                        $useOptionStatementName,
                        $parentResolvedScenarioName,
                        $item->getResolvedScenarioStatementName()
                    );

                    throw new \RuntimeException($message);
                }

                $values = $useOption->getValues();

                if (empty($values)) {
                    $message = sprintf(
                        'Invalid \'use\' option statement values. \'values\' entry is empty in scenario \'%s\' for parent statement \'%s\'',
                        $parentResolvedScenarioName,
                        $item->getResolvedScenarioStatementName()
                    );

                    throw new \RuntimeException($message);
                }
            }
        }
    }
}