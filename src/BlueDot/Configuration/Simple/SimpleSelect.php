<?php

namespace BlueDot\Configuration\Simple;

use BlueDot\Exception\ConfigurationException;

class SimpleSelect
{
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var string $selectStatement
     */
    private $statement;
    /**
     * @var array parameters
     */
    private $parameters;
    /**
     * @param array $configuration
     * @throws ConfigurationException
     */
    public function __construct(array $configuration)
    {
        $keys = array_keys($configuration);

        if (empty($keys)) {
            throw new ConfigurationException('If provided, \'select\' should have the name as the first entry and optional \'parameters\' as the last entry in the configuration');
        }

        if (!isset($keys[0])) {
            throw new ConfigurationException('If provided, \'select\' should have the name as the first entry and optional \'parameters\' as the last entry in the configuration');
        }

        $this->name = $keys[0];

        $this->statement = $configuration[$this->name];

        if (isset($keys[1])) {
            if ($keys[1] !== 'parameters') {
                throw new ConfigurationException('Unknown configuration under configuration->simple->select. Expected \'parameters\'');
            }

            $this->parameters = $configuration['parameters'];
        }
    }
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return string
     */
    public function getSelectStatement() : string
    {
        return $this->statement;
    }
    /**
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
}