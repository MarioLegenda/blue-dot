<?php

namespace BlueDot\Configuration\Simple;

use BlueDot\Exception\ConfigurationException;

class StatementFactory
{
    public static function createSimpleStatements(array $simples) : array
    {
        $createdSimples = array();

        $validTypes = array('select', 'insert', 'update', 'delete');
        $statementTypes = array_keys($simples);

        foreach ($statementTypes as $statementType) {
            if (in_array($statementType, $validTypes) === false) {
                throw new ConfigurationException('Invalid query type. Valid types are '.implode(', ', $validTypes));
            }
        }

        foreach ($simples as $simpleType => $statements) {
            foreach ($statements as $statementName => $statement) {
                $parameters = array();

                if (!array_key_exists('sql', $statement)) {
                    throw new ConfigurationException('No SQL statement found in configuration under \'sql\'');
                }

                if (array_key_exists('parameters', $statement)) {
                    if (!is_array($statement['parameters'])) {
                        throw new ConfigurationException('Invalid configuration. If provided, \'parameters\' should be an array');
                    }

                    $parameters = $statement['parameters'];
                }

                $createdSimples[] = new Statement($simpleType, $statementName, $statement['sql'], $parameters);
            }
        }

        return $createdSimples;
    }
}