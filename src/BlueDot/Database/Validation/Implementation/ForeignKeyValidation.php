<?php

namespace BlueDot\Database\Validation\Implementation;

use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Flow\Scenario\ForeignKey;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Database\Validation\ValidatorInterface;

class ForeignKeyValidation implements ValidatorInterface
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

            $this->validateForeignKeys($metadata);
        }
    }
    /**
     * @param array $metadata
     * @throws \RuntimeException
     */
    private function validateForeignKeys(array $metadata)
    {
        /** @var Metadata $item */
        foreach ($metadata as $item) {
            /** @var ForeignKey $foreignKey */
            $foreignKey = $item->getForeignKey();
            $parentResolvedScenarioName = $this->configuration->getRootConfiguration()->getScenarioName();

            if ($foreignKey instanceof ForeignKey) {
                $foreignKeyStatementName = $foreignKey->getStatementName();

                if (!array_key_exists($foreignKeyStatementName, $metadata)) {
                    $message = sprintf(
                        'Invalid \'foreign_key\' option statement name. Statement \'%s\' does not exist in scenario \'%s\' for parent statement \'%s\'',
                        $foreignKeyStatementName,
                        $parentResolvedScenarioName,
                        $item->getResolvedScenarioStatementName()
                    );

                    throw new \RuntimeException($message);
                }
            }
        }
    }
}