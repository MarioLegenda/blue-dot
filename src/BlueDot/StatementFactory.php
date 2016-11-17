<?php

namespace BlueDot;

use BlueDot\Common\ArgumentBag;
use BlueDot\Configuration\Scenario\ForeginKey;
use BlueDot\Exception\ConfigurationException;

use BlueDot\Configuration\Scenario\ScenarioStatement;
use BlueDot\Configuration\Scenario\ScenarioStatementCollection;
use BlueDot\Configuration\Scenario\UseOption;
use BlueDot\Configuration\Simple\SimpleStatement;

class StatementFactory
{
    public static function createSimpleStatements(array $simples) : array
    {
        $createdSimples = array();

        $validTypes = array('select', 'insert', 'update', 'delete', 'table', 'database');
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

                $arguments = new ArgumentBag();
                $arguments
                    ->add('type', $simpleType)
                    ->add('name', $statementName)
                    ->add('resolved_name', $simpleType.'.'.$statementName)
                    ->add('sql', $statement['sql'])
                    ->add('parameters', $parameters);
                $createdSimples[] = new SimpleStatement($arguments);
            }
        }

        return $createdSimples;
    }

    public static function createScenarioStatements(array $scenarios) : ScenarioStatementCollection
    {
        $createdScenarios = new ScenarioStatementCollection();

        $scenarioNames = array_keys($scenarios);

        foreach ($scenarioNames as $scenarioName) {
            $scenarioCluster = $scenarios[$scenarioName];

            $atomic = false;

            if (array_key_exists('atomic', $scenarioCluster)) {
                $atomic = (is_bool($scenarioCluster['atomic'])) ? $scenarioCluster['atomic'] : false;
                unset($scenarioCluster['atomic']);
            }

            foreach ($scenarioCluster as $statementName => $scenarioStatement) {
                $resolvedName = 'scenario.'.$scenarioName.'.'.$statementName;

                if (!array_key_exists('sql', $scenarioStatement)) {
                    throw new ConfigurationException('Invalid configuration. A scenario statement should have an \'sql\' value of '.$resolvedName);
                }

                $sql = $scenarioStatement['sql'];

                if (!is_string($sql)) {
                    throw new ConfigurationException('Sql statement has to be a string of scenario.'.$scenarioName.'.'.$statementName);
                }

                if (array_key_exists('parameters', $scenarioStatement)) {
                    $parameters = $scenarioStatement['parameters'];

                    if (!is_array($parameters)) {
                        throw new ConfigurationException('\'parameters\' configuration entry should be an array');
                    }
                }

                $arguments = new ArgumentBag();
                $arguments
                    ->add('type', 'scenario')
                    ->add('scenario_name', $scenarioName)
                    ->add('name', $statementName)
                    ->add('resolved_name', 'scenario.'.$scenarioName.'.'.$statementName)
                    ->add('sql', $sql)
                    ->add('parameters', (isset($parameters)) ? $parameters : array());
                $scenarioEntry = new ScenarioStatement($arguments);

                $scenarioEntry->setAtomic($atomic);

                if (array_key_exists('use', $scenarioStatement)) {
                    $useOption = $scenarioStatement['use'];

                    if (!array_key_exists('name', $useOption) and !array_key_exists('values', $useOption)) {
                        throw new ConfigurationException('\'use\' configuration value should \'name\' and \'values\' configuration values under itself');
                    }

                    if (!$createdScenarios->hasScenarioStatement($scenarioName, $useOption['name'])) {
                        throw new ConfigurationException('Invalid scenario configuration for '.$resolvedName.'. scenario.'.$scenarioName.'.'.$useOption['name'].' should be before the statement that uses it ('.$resolvedName.')');
                    }

                    $statementType = strtolower(substr($createdScenarios->getScenarioStatement($scenarioName, $useOption['name'])->getStatement(), 0, 6));

                    if ($statementType !== 'select' and $statementType !== 'insert') {
                        throw new ConfigurationException('An sql statement can only \'use\' a \'select\' sql query');
                    }

                    $values = $useOption['values'];

                    if (!is_string($values) and !is_array($values)) {
                        throw new ConfigurationException('\'values\' configuration entry under \'use\' can only be a string or an array for ', $resolvedName);
                    }

                    if (is_string($values)) {
                        $exploded = explode('.', $values);

                        if (count($exploded) !== 2) {
                            throw new ConfigurationException('Invalid \'use\' configuration of '.$resolvedName);
                        }

                        if (!$createdScenarios->hasScenarioStatement($scenarioName, $exploded[0])) {
                            throw new ConfigurationException('Unknown scenario statement'.$exploded[0].' in \'use\' configuration entry of '.$resolvedName);
                        }

                        $values = $exploded;
                    }

                    $scenarioEntry->setUseOption(new UseOption($useOption['name'], $values));
                }

                if (array_key_exists('foreign_key', $scenarioStatement)) {
                    $foreignKey = $scenarioStatement['foreign_key'];

                    if (!array_key_exists('statement_name', $foreignKey)) {
                        throw new ConfigurationException('\'foreign_key\' configuration entry of '.$resolvedName.' has to have a \'statement_name\' configuration entry under itself');
                    }

                    if (!array_key_exists('bind_to', $foreignKey)) {
                        throw new ConfigurationException('\'foreign_key\' configuration entry of '.$resolvedName.' has to have a \'bind_to\' configuration entry under itself');
                    }

                    if (!is_array($foreignKey['bind_to'])) {
                        throw new ConfigurationException($resolvedName.'.foreign_key.bind_to should be an associative array where key is the name of the column that is inserted and value is the value name of the column that is used to created the foreign key');
                    }

                    if (!$createdScenarios->hasScenarioStatement($scenarioName, $foreignKey['statement_name'])) {
                        throw new ConfigurationException('scenario.'.$scenarioName.'.'.$foreignKey['statement_name'].' has to be declared before '.$resolvedName);
                    }

                    $scenarioEntry->setForeignKey(new ForeginKey($foreignKey['statement_name'], $foreignKey['bind_to']));
                }

                $createdScenarios->add($scenarioName, $scenarioEntry);
            }
        }

        return $createdScenarios;
    }
}