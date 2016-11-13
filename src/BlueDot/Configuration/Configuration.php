<?php

namespace BlueDot\Configuration;

use BlueDot\Configuration\Simple\SimpleSelect;
use BlueDot\Configuration\Simple\StatementFactory;
use BlueDot\Exception\ConfigurationException;

class Configuration
{
    /**
     * @var array $connectionType
     */
    private $dsn = array();
    /**
     * @var array $simples
     */
    private $simples = array();
    /**
     * @constructor
     * @param array $configuration
     * @throws ConfigurationException
     */
    public function __construct(array $configuration)
    {
        if (!array_key_exists('configuration', $configuration)) {
            throw new ConfigurationException('Invalid configuration file. Top element should be \'configuration\'');
        }

        $configuration = $configuration['configuration'];

        if (array_key_exists('connection', $configuration)) {
            $connection = $configuration['connection'];

            $validKeys = array('host', 'database_name', 'user', 'password');

            foreach ($validKeys as $key) {
                if (!array_key_exists($key, $connection)) {
                    throw new ConfigurationException('Invalid connection configuration. Missing '.$key.' configutaion value');
                }
            }

            $this->dsn = $connection;
        }

        if (array_key_exists('simple', $configuration)) {
            $simples = $configuration['simple'];

            $this->simples = StatementFactory::createSimpleStatements($configuration['simple']);
        }
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function findSimpleByName(string $name)
    {
        foreach ($this->simples as $simple) {
            if ($simple->getName() === $name) {
                return $simple;
            }
        }

        return null;
    }
    /**
     * @return string
     */
    public function getDsn() : array
    {
        return $this->dsn;
    }
}