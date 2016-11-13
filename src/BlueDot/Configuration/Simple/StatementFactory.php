<?php

namespace BlueDot\Configuration\Simple;

use BlueDot\Exception\ConfigurationException;

class StatementFactory
{
    public static function createSimpleStatements(array $simples) : array
    {
        $createdSimples = array();

        foreach ($simples as $simpleType => $statement) {
            $keys = array_keys($statement);

            if (empty($keys)) {
                throw new ConfigurationException('If provided, \'select\' should have the name as the first entry and optional \'parameters\' as the last entry in the configuration');
            }

            if (!isset($keys[0])) {
                throw new ConfigurationException('If provided, \'select\' should have the name as the first entry and optional \'parameters\' as the last entry in the configuration');
            }

            $name = $keys[0];
            $values = $statement[$name];
            $parameters = array();

            if (!array_key_exists('sql', $values)) {
                throw new ConfigurationException('No SQL statement found in configuration under \'sql\'');
            }

            if (array_key_exists('parameters', $values)) {
                if (!is_array($values['parameters'])) {
                    throw new ConfigurationException('Invalid configuration. If provided, \'parameters\' should be an array');
                }

                $parameters = $values['parameters'];
            }

            $createdSimples[] = new Statement($name, $values['sql'], $parameters);
        }

        return $createdSimples;
    }
}