<?php

namespace BlueDot\Configuration\Finder;

use BlueDot\Common\FlowProductInterface;

class ServiceStatementFinder
{
    /**
     * @var array $configurations
     */
    private $configurations = [];
    /**
     * @param string $name
     * @param FlowProductInterface $configuration
     * @return ServiceStatementFinder
     */
    public function add(string $name, FlowProductInterface $configuration): ServiceStatementFinder
    {
        $this->configurations[$name] = $configuration;

        return $this;
    }
    /**
     * @param string $name
     * @return FlowProductInterface
     */
    public function find(string $name): ?FlowProductInterface
    {
        if (array_key_exists($name, $this->configurations)) {
            return $this->configurations[$name];
        }

        return null;
    }
}