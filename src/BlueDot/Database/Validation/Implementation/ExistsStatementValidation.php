<?php

namespace BlueDot\Database\Validation\Implementation;

use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Database\Validation\ValidatorInterface;

class ExistsStatementValidation implements ValidatorInterface
{
    /**
     * @var FlowProductInterface|ScenarioConfiguration $configuration
     */
    private $configuration;
    /**
     * UseOptionValidation constructor.
     * @param FlowProductInterface $configuration
     */
    public function __construct(
        FlowProductInterface $configuration
    ) {
        $this->configuration = $configuration;
    }
    /**
     * @inheritdoc
     */
    public function validate()
    {
        if ($this->configuration instanceof ScenarioConfiguration) {
            /** @var Metadata[] $metadata */
            $metadata = $this->configuration->getMetadata();

            $this->validateExistsStatement($metadata);
        }
    }
    /**
     * @param Metadata[] $metadata
     */
    private function validateExistsStatement(array $metadata)
    {
        /** @var Metadata $item */
        foreach ($metadata as $item) {
            $ifExistsStatementName = $item->getIfExistsStatementName();
            $ifNotExistsStatementName = $item->getIfNotExistsStatementName();
            $parentResolvedStatementName = $item->getResolvedScenarioStatementName();
            $scenarioName = $this->configuration->getRootConfiguration()->getScenarioName();

            if (is_string($ifExistsStatementName) and is_string($ifNotExistsStatementName)) {
                $message = sprintf(
                    'Invalid \'if_exists\' and \'if_not_exists\' options. You cannot use both \'if_exists\' and \'if_not_exists\' for parent statement \'%s\' in scenario \'%s\'',
                    $parentResolvedStatementName,
                    $scenarioName
                );

                throw new \RuntimeException($message);
            }

            if (is_string($ifExistsStatementName)) {
                if (!array_key_exists($ifExistsStatementName, $metadata)) {
                    $message = sprintf(
                        'Invalid \'if_exists\' option statement name. Statement \'%s\' does not exists in scenario \'%s\' for parent statement \'%s\'',
                        $ifExistsStatementName,
                        $scenarioName,
                        $parentResolvedStatementName
                    );

                    throw new \RuntimeException($message);
                }
            }

            if (is_string($ifNotExistsStatementName)) {
                if (!array_key_exists($ifNotExistsStatementName, $metadata)) {
                    $message = sprintf(
                        'Invalid \'if_not_exists\' option statement name. Statement \'%s\' does not exists in scenario \'%s\' for parent statement \'%s\'',
                        $ifNotExistsStatementName,
                        $scenarioName,
                        $parentResolvedStatementName
                    );

                    throw new \RuntimeException($message);
                }
            }
        }
    }
}