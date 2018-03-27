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
        $workConfig = $this->configuration->getWorkConfig();

        $configParameters = $workConfig->getConfigParameters();
        $userParameters = $workConfig->getUserParameters();

        if (!empty($configParameters)) {
            $diff = array_diff($configParameters, $userParameters);

            if (!empty($diff)) {
                
            }
        }
    }
}