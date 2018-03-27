<?php

namespace BlueDot\Configuration\Finder;

use BlueDot\Common\ArgumentBag;
use BlueDot\Database\Model\ConfigurationInterface;

class SimpleStatementFinder
{
    /**
     * @var array $configurations
     */
    private $configurations = [];
    /**
     * @param string $name
     * @param ConfigurationInterface $configuration
     * @return SimpleStatementFinder
     */
    public function add(string $name, ConfigurationInterface $configuration): SimpleStatementFinder
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