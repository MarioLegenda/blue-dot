<?php

namespace BlueDot\Configuration;

use BlueDot\StatementFactory;
use BlueDot\Exception\ConfigurationException;

class MainConfiguration
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
     * @var array $compounds
     */
    private $compounds = array();
    /**
     * @var array $foundStatements
     */
    private $foundStatements = array();
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
            $this->simples = StatementFactory::createSimpleStatements($configuration['simple']);
        }

        if (array_key_exists('compound', $configuration)) {
            $this->compounds = StatementFactory::createCompoundStatements($configuration['compound']);
        }
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function findSimpleByName(string $name)
    {
        if (array_key_exists($name, $this->foundStatements)) {
            return $this->foundStatements[$name];
        }

        $exploded = explode('.', $name);

        if (count($exploded) === 2) {
            $statementType = $exploded[0];
            $statementName = $exploded[1];
            foreach ($this->simples as $simple) {
                if ($simple->getType() === $statementType) {
                    if ($simple->getName() === $statementName) {
                        $this->foundStatements[$name] = $simple;
                        return $simple;
                    }
                }
            }

            throw new ConfigurationException('Query with name '.$name.' has not been found in the configuration under '.$statementType.' statement type. This could be an internal error so please contact the creator of this tool at whitepostmail@gmail.com');
        }

        foreach ($this->simples as $simple) {
            if ($simple->getName() === $name) {
                $this->foundStatements[$name] = $simple;
                return $simple;
            }
        }

        throw new ConfigurationException('Query with name '.$name.' has not been found in the configuration. This could be an internal error so please contact the creator of this tool at whitepostmail@gmail.com');
    }
    /**
     * @return string
     */
    public function getDsn() : array
    {
        return $this->dsn;
    }
}