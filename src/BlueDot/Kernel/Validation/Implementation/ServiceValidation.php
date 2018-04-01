<?php

namespace BlueDot\Kernel\Validation\Implementation;

use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Kernel\Validation\ValidatorInterface;

class ServiceValidation implements ValidatorInterface
{
    /**
     * @var FlowConfigurationProductInterface|ServiceConfiguration $configuration
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
     * @void
     */
    public function validate()
    {
        if ($this->configuration instanceof ServiceConfiguration) {
            $this->validateServiceConfiguration();
        }
    }
    /**
     * @throws \RuntimeException
     */
    private function validateServiceConfiguration()
    {
        $class = $this->configuration->getClass();

        if (!class_exists($class)) {
            $message = sprintf(
                'Invalid service configuration. Class \'%s\' does not exist',
                $class
            );

            throw new \RuntimeException($message);
        }
    }
}