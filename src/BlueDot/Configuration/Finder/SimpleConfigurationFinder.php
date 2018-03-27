<?php

namespace BlueDot\Configuration\Finder;

use BlueDot\Database\Model\ConfigurationInterface;

class SimpleConfigurationFinder
{
    /**
     * @var array $configurations
     */
    private $configurations = [];
    /**
     * @param string $name
     * @param ConfigurationInterface $configuration
     * @return SimpleConfigurationFinder
     */
    public function add(string $name, ConfigurationInterface $configuration): SimpleConfigurationFinder
    {
        $this->configurations[$name] = $configuration;

        return $this;
    }
    /**
     * @param string $name
     * @return ConfigurationInterface|null
     */
    public function find(string $name): ?ConfigurationInterface
    {
        if (array_key_exists($name, $this->configurations)) {
            return $this->configurations[$name];
        }

        return null;
    }
}