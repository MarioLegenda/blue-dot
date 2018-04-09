<?php

namespace BlueDot\Kernel\Validation\Implementation;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Configuration\Flow\Enum\MultipleParametersType;
use BlueDot\Configuration\Flow\Enum\SingleParameterType;
use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Kernel\Validation\ValidatorInterface;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;

class BasicCorrectParametersValidation implements ValidatorInterface
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
            $userParametersType = $workConfig->getUserParametersType();

            $this->handleGenericParametersValidation(
                $userParameters,
                $configParameters,
                $resolvedStatementName
            );

            $this->handleParametersTypeValidation(
                $userParameters,
                $resolvedStatementName,
                $userParametersType
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
     * @param array|null|object $configParameters
     * @param string $resolvedStatementName
     * @throws \RuntimeException
     */
    private function handleGenericParametersValidation(
        array $userParameters,
        array $configParameters,
        string $resolvedStatementName
    ) {
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
    private function handleParametersEquality(
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
                $resolvedStatementName,
                implode(', ', $diff)
            );

            throw new \RuntimeException($message);
        }
    }
    /**
     * @param array $userParameters
     * @param string $resolvedStatementName
     * @param TypeInterface|null $userParametersType
     * @throws \RuntimeException
     * @return null
     */
    private function handleParametersTypeValidation(
        array $userParameters,
        string $resolvedStatementName,
        TypeInterface $userParametersType = null
    ) {
        if (!$userParametersType instanceof TypeInterface) {
            return null;
        }

        if ($userParametersType->equals(SingleParameterType::fromValue())) {
            $this->validateSingleKeys($userParameters, $resolvedStatementName);
        }

        if ($userParametersType->equals(MultipleParametersType::fromValue())) {
            foreach ($userParameters as $index => $userParameter) {
                if (!is_int($index)) {
                    $message = sprintf(
                        'Invalid parameters. If you choose for one statement to make multiple inserts/updates/deletes, then parameters have to be a multidimensional array containing single parameters. If you choose single insert/update/delete, then parameters have to be a single associative string array in statement \'%s\'',
                        $resolvedStatementName
                    );

                    throw new \RuntimeException($message);
                }

                if (!is_array($userParameter)) {
                    $message = sprintf(
                        'Invalid parameters. If you choose for one statement to make multiple inserts/updates/deletes, then parameters have to be a multidimensional array containing single parameters. If you choose single insert/update/delete, then parameters have to be a single associative string array in statement \'%s\'',
                        $resolvedStatementName
                    );

                    throw new \RuntimeException($message);
                }

                $this->validateSingleKeys($userParameter, $resolvedStatementName);
            }
        }
    }
    /**
     * @param array $userParameters
     * @param string $resolvedStatementName
     * @throws \RuntimeException
     */
    private function validateSingleKeys(
        array $userParameters,
        string $resolvedStatementName
    ) {
        $userParameterKeys = array_keys($userParameters);

        foreach ($userParameterKeys as $key) {
            if (!is_string($key)) {
                $message = sprintf(
                    'Invalid parameters. If you choose for one statement to make multiple inserts/updates/deletes, then parameters have to be a multidimensional array containing single parameters. If you choose single insert/update/delete, then parameters have to be a single associative string array in statement \'%s\'',
                    $resolvedStatementName
                );

                throw new \RuntimeException($message);
            }

            $parameterValue = $userParameters[$key];

            if (
                is_array($parameterValue) or
                is_object($parameterValue) or
                is_resource($parameterValue) or
                empty($parameterValue)
            ) {
                $message = sprintf(
                    'Invalid parameter. Parameter value cannot be an object, array, resource or an empty value in statement \'%s\'',
                    $resolvedStatementName
                );

                throw new \RuntimeException($message);
            }
        }
    }
}
