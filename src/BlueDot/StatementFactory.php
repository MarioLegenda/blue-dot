<?php

namespace BlueDot;

use BlueDot\Configuration\Compound\ForeginKey;
use BlueDot\Exception\ConfigurationException;

use BlueDot\Configuration\Compound\CompoundStatement;
use BlueDot\Configuration\Compound\CompoundStatementCollection;
use BlueDot\Configuration\Compound\UseOption;
use BlueDot\Configuration\Simple\SimpleStatement;

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

                $createdSimples[] = new SimpleStatement($simpleType, $statementName, $statement['sql'], $parameters);
            }
        }

        return $createdSimples;
    }

    public static function createCompoundStatements(array $compounds)
    {
        $createdCompounds = new CompoundStatementCollection();

        $compoundNames = array_keys($compounds);

        foreach ($compoundNames as $compoundName) {
            $compoundCluster = $compounds[$compoundName];

            $atomic = false;

            if (array_key_exists('atomic', $compoundCluster)) {
                $atomic = (is_bool($compoundCluster['atomic'])) ? $compoundCluster['atomic'] : false;
                unset($compoundCluster['atomic']);
            }

            foreach ($compoundCluster as $statementName => $compoundStatement) {
                $resolvedName = 'compound.'.$compoundName.'.'.$statementName;

                if (!array_key_exists('sql', $compoundStatement)) {
                    throw new ConfigurationException('Invalid configuration. A compound statement should have an \'sql\' value');
                }

                $sql = $compoundStatement['sql'];

                if (array_key_exists('parameters', $compoundStatement)) {
                    $parameters = $compoundStatement['parameters'];

                    if (!is_array($parameters)) {
                        throw new ConfigurationException('\'parameters\' configuration entry should be an array');
                    }
                }

                $compoundEntry = new CompoundStatement(
                    '',
                    $statementName,
                    $sql,
                    (isset($parameters)) ? $parameters : array()
                );

                $compoundEntry->setAtomic($atomic);

                if (array_key_exists('use', $compoundStatement)) {
                    $useOption = $compoundStatement['use'];

                    if (!array_key_exists('name', $useOption) and !array_key_exists('values', $useOption)) {
                        throw new ConfigurationException('\'use\' configuration value should \'name\' and \'values\' configuration values under itself');
                    }

                    if (!$createdCompounds->hasCompound($compoundName, $useOption['name'])) {
                        throw new ConfigurationException('Invalid compound configuration for '.$resolvedName.'. compound.'.$compoundName.'.'.$useOption['name'].' should be before the statement that uses it ('.$resolvedName.')');
                    }

                    $compoundEntry->setUseOption(new UseOption($useOption['name'], $useOption['values']));
                }

                if (array_key_exists('foreign_key', $compoundStatement)) {
                    $foreignKey = $compoundStatement['foreign_key'];

                    if (!array_key_exists('statement_name', $foreignKey)) {
                        throw new ConfigurationException('\'foreign_key\' configuration entry of '.$resolvedName.' has to have a \'statement_name\' configuration entry under itself');
                    }

                    if (!array_key_exists('bind_to', $foreignKey)) {
                        throw new ConfigurationException('\'foreign_key\' configuration entry of '.$resolvedName.' has to have a \'bind_to\' configuration entry under itself');
                    }

                    if (!is_array($foreignKey['bind_to'])) {
                        throw new ConfigurationException($resolvedName.'.foreign_key.bind_to should be an associative array where key is the name of the column that is inserted and value is the value name of the column that is used to created the foreign key');
                    }

                    if (!$createdCompounds->hasCompound($compoundName, $foreignKey['statement_name'])) {
                        throw new ConfigurationException('compound.'.$compoundName.'.'.$foreignKey['statement_name'].' has to be declared before '.$resolvedName);
                    }

                    $compoundEntry->setForeignKey(new ForeginKey($foreignKey['statement_name'], $foreignKey['bind_to']));
                }

                $createdCompounds->add($compoundName, $compoundEntry);
            }
        }
    }
}