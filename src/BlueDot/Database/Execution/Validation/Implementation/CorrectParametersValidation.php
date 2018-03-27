<?php

namespace BlueDot\Database\Execution\Validation\Implementation;

use BlueDot\Database\Execution\Validation\ValidatorInterface;
use BlueDot\Database\Model\ConfigurationInterface;
use BlueDot\Database\Model\Simple\SimpleConfiguration;

class CorrectParametersValidation implements ValidatorInterface
{
    /**
     * @var ConfigurationInterface|SimpleConfiguration $configuration
     */
    private $configuration;
    /**
     * CorrectSqlValidation constructor.
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $this->handleGenericParametersValidation();
        $this->handleParametersEquality();
    }
    /**
     * @throws \RuntimeException
     */
    private function handleGenericParametersValidation()
    {
        $workConfig = $this->configuration->getWorkConfig();

        $configParameters = $workConfig->getConfigParameters();
        $userParameters = $workConfig->getUserParameters();
        $userParameterKeys = array_keys($userParameters);

        foreach ($userParameterKeys as $key) {
            if (!is_string($key)) {
                $message = sprintf(
                    'User parameters have to be an associative array. Non string key given for user parameters with keys \'%s\'',
                    implode(', ', $userParameterKeys)
                );

                throw new \RuntimeException($message);
            }
        }

        if (!empty($configParameters) and empty($userParameters)) {
            $message = sprintf(
                'You supplied config parameters but no user parameters. Config parameters are \'%s\'',
                implode(', ', array_keys($configParameters))
            );

            throw new \RuntimeException($message);
        }

        if (empty($configParameters) and !empty($userParameters)) {
            $message = sprintf(
                'You supplied user parameters but no config parameters. User parameters are \'%s\'',
                implode(', ', array_keys($userParameters))
            );

            throw new \RuntimeException($message);
        }
    }
    /**
     * @throws \RuntimeException
     */
    public function handleParametersEquality()
    {
        $workConfig = $this->configuration->getWorkConfig();

        $configParameters = $workConfig->getConfigParameters();
        $userParameters = $workConfig->getUserParameters();

        $configParametersKeys = array_values($configParameters);
        $userParametersKeys = array_keys($userParameters);

        $diff = array_diff($userParametersKeys, $configParametersKeys);

        if (!empty($diff)) {
            $message = sprintf(
                'Some user parameters are missing but you supplied them as config parameters. Config parameters are \'%s\'',
                implode(', ', $diff)
            );

            throw new \RuntimeException($message);
        }
    }
}