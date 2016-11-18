<?php

namespace BlueDot\Configuration;

use BlueDot\Configuration\Validator\ConfigurationValidator;

class ConfigurationBuilder
{
    /**
     * @var array $resolvedConfiguration
     */
    private $builtConfiguration = array();
    /**
     * @var array $rawConfiguration
     */
    private $rawConfiguration;
    /**
     * @param ConfigurationValidator $validator
     */
    public function __construct(ConfigurationValidator $validator)
    {
        $this->rawConfiguration = $validator->validate()->getConfiguration();
    }
    /**
     * @return $this
     */
    public function buildConfiguration() : ConfigurationBuilder
    {
        return $this;
    }
    /**
     * @return array
     */
    public function getConfiguration() : array
    {
        return $this->builtConfiguration;
    }
}