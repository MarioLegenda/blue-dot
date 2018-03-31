<?php

namespace BlueDot\Database\Execution\Validation\Implementation;

use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Database\Execution\Validation\ValidatorInterface;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;

class CorrectParametersValidation implements ValidatorInterface
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
            $workConfig = $this->configuration->getWorkConfig();

            $resolvedStatementName = $this->configuration->getMetadata()->getResolvedStatementName();
            $userParameters = $workConfig->getUserParameters();
            $configParameters = $workConfig->getConfigParameters();

            $this->handleGenericParametersValidation(
                $userParameters,
                $configParameters,
                $resolvedStatementName
            );

            $this->handleParametersEquality(
                $userParameters,
                $configParameters,
                $resolvedStatementName
            );
        }

        if ($this->configuration instanceof ScenarioConfiguration) {
			/** @var Metadata[] $metadata */
			$metadata = $this->configuration->getMetadata();

			/** @var Metadata $item */
			foreach ($metadata as $item) {
				$userParameters = $item->getUserParameters();
				$configParameters = $item->getConfigParameters();
				$resolvedStatementName = $item->getResolvedScenarioStatementName();

				$this->handleGenericParametersValidation(
				    $userParameters,
                    $configParameters,
                    $resolvedStatementName
                );

				$this->handleParametersEquality(
				    $userParameters,
                    $configParameters,
                    $resolvedStatementName
                );
			}
        }
    }
    /**
     * @param array $userParameters
     * @param array $configParameters
     * @param string $resolvedStatementName
     * @throws \RuntimeException
     */
    private function handleGenericParametersValidation(
        array $userParameters,
        array $configParameters,
        string $resolvedStatementName
    ) {
        $userParameterKeys = array_keys($userParameters);

        foreach ($userParameterKeys as $key) {
            if (!is_string($key)) {
                $message = sprintf(
                    'User parameters have to be an associative array. Non string key given for statement \'%s\' with user parameters with keys \'%s\'',
                    $resolvedStatementName,
                    implode(', ', $userParameterKeys)
                );

                throw new \RuntimeException($message);
            }
        }

        if (!empty($configParameters) and empty($userParameters)) {
            $message = sprintf(
                'You supplied config parameters but no user parameters. Config parameters are \'%s\' for statement \'%s\'',
                implode(', ', array_values($configParameters)),
                $resolvedStatementName
            );

            throw new \RuntimeException($message);
        }

        if (empty($configParameters) and !empty($userParameters)) {
            $message = sprintf(
                'You supplied user parameters but no config parameters. User parameters are \'%s\' for statement \'%s\'',
                implode(', ', array_keys($userParameters)),
                $resolvedStatementName
            );

            throw new \RuntimeException($message);
        }
    }
    /**
     * @param array $userParameters
     * @param array $configParameters
     * @param string $resolvedStatementName
     * @throws \RuntimeException
     */
    public function handleParametersEquality(
        array $userParameters,
        array $configParameters,
        string $resolvedStatementName
    ) {

        $configParametersKeys = array_values($configParameters);
        $userParametersKeys = array_keys($userParameters);

        $diff = array_diff($userParametersKeys, $configParametersKeys);

        if (!empty($diff)) {
            $message = sprintf(
                'Some user parameters are missing but you supplied them as config parameters for statement \'%s\'. Config parameters are \'%s\'',
                implode(', ', $diff),
                $resolvedStatementName
            );

            throw new \RuntimeException($message);
        }
    }
}
