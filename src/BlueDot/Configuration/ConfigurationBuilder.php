<?php

namespace BlueDot\Configuration;

use BlueDot\Common\ArgumentBag;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\Connection;
use BlueDot\Database\Parameter\Parameter;
use BlueDot\Database\Parameter\ParameterCollection;
use BlueDot\Database\Scenario\ForeginKey;
use BlueDot\Database\Scenario\UseOption;

class ConfigurationBuilder
{
    /**
     * @var array $resolvedConfiguration
     */
    private $builtConfiguration = array();
    /**
     * @var array $rawConfiguration
     */
    private $rawConfiguration;
    /**
     * @param ConfigurationValidator $validator
     */
    public function __construct(ConfigurationValidator $validator)
    {
        $this->rawConfiguration = $validator->validate()->getConfiguration();
    }
    /**
     * @return $this
     */
    public function buildConfiguration() : ConfigurationBuilder
    {
        $configuration = $this->rawConfiguration['configuration'];

        $this->builtConfiguration['simple'] = $this->buildSimpleConfiguration($configuration['simple']);
        $this->builtConfiguration['scenario'] = $this->buildScenarioConfiguration($configuration['scenario']);
        $this->builtConfiguration['connection'] = $this->buildConnection($configuration['connection']);
        $this->builtConfiguration['callable'] = $this->buildCallableConfiguration($configuration['callable']);
        return $this;
    }
    /**
     * @return array
     */
    public function getConfiguration() : array
    {
        return $this->builtConfiguration;
    }

    private function buildConnection(array $connection) : Connection
    {
        return new Connection($connection);
    }

    private function buildSimpleConfiguration(array $simpleConfiguration)
    {
        $builtSimpleConfiguration = new ArgumentBag();

        foreach ($simpleConfiguration as $type => $typeConfig) {
            foreach ($typeConfig as $statementName => $statementConfig) {
                $builtStatement = new ArgumentBag();
                $builtStatement->add('type', 'simple');
                $builtStatement->add('resolved_name', $type.'.'.$statementName);
                $builtStatement->add('statement_type', $type);
                $builtStatement->add('statement_name', $statementName);

                $workConfig = new ArgumentBag();
                $workConfig->add('sql', $statementConfig['sql']);

                if (array_key_exists('parameters', $statementConfig)) {
                    $parameters = $statementConfig['parameters'];

                    $workConfig->add('parameters', $this->addSimpleParameters($parameters));
                }

                $builtStatement->mergeStorage($workConfig);

                $builtSimpleConfiguration->add('simple.'.$builtStatement->get('resolved_name'), $builtStatement);
            }
        }

        return $builtSimpleConfiguration;
    }

    private function buildScenarioConfiguration(array $scenarioConfiguration)
    {
        $mainScenario = new ArgumentBag();

        foreach ($scenarioConfiguration as $scenarioName => $scenarioConfigs) {
            $scenarioStatements = $scenarioConfigs['statements'];
            $resolvedScenarioName = 'scenario.'.$scenarioName;

            $builtScenarioConfiguration = new ArgumentBag();
            $builtScenarioConfiguration->add('type', 'scenario');

            $rootConfig = new ArgumentBag();
            $rootConfig->add('atomic', $scenarioConfigs['atomic']);
            $rootConfig->add('returns', $scenarioConfigs['return']);

            $statemens = new ArgumentBag();
            foreach ($scenarioStatements as $statementName => $statementConfig) {
                $resolvedStatementName = 'scenario.'.$scenarioName.'.'.$statementName;

                $scenarioStatement = new ArgumentBag();
                $scenarioStatement->add('resolved_statement_name', $resolvedStatementName);
                $scenarioStatement->add('statement_name', $statementName);
                $scenarioStatement->add('sql', $statementConfig['sql']);



                if (array_key_exists('parameters', $statementConfig)) {
                    $parameters = $statementConfig['parameters'];

                    $scenarioStatement->add('parameters', $this->addScenarioParameters($parameters));
                }

                if (array_key_exists('use', $statementConfig)) {
                    $useOption = $statementConfig['use'];

                    $scenarioStatement->add(
                        'use_option',
                        new UseOption($useOption['name'], $useOption['values'])
                    );
                }

                if (array_key_exists('foreign_key', $statementConfig)) {
                    $foreignKey = $statementConfig['foreign_key'];

                    $scenarioStatement->add(
                        'foreign_key',
                        new ForeginKey($foreignKey['statement_name'], $foreignKey['bind_to'])
                    );
                }

                $statemens->add($resolvedStatementName, $scenarioStatement);
            }

            $builtScenarioConfiguration->add('root_config', $rootConfig);
            $builtScenarioConfiguration->add('statements', $statemens);

            $mainScenario->add($resolvedScenarioName, $builtScenarioConfiguration);
        }

        return $mainScenario;
    }

    private function buildCallableConfiguration(array $callableConfiguration) : ArgumentBag
    {
        $callableConfig = new ArgumentBag();

        foreach ($callableConfiguration as $key => $config) {
            $subConfig = new ArgumentBag();

            $subConfig->add('type', $config['type']);
            $subConfig->add('name', $config['name']);

            $callableConfig->add($key, $subConfig);
        }

        return $callableConfig;
    }

    private function addSimpleParameters(array $parameters) : ParameterCollection
    {
        $parameterCollection = new ParameterCollection();

        foreach ($parameters as $parameter) {
            $parameterCollection->addParameter(new Parameter($parameter));
        }

        return $parameterCollection;
    }

    private function addScenarioParameters(array $parameters) : ParameterCollection
    {
        $parameterCollection = new ParameterCollection();

        foreach ($parameters as $key => $parameter) {
            $parameterCollection = new ParameterCollection();

            foreach ($parameters as $parameter) {
                $parameterCollection->addParameter(new Parameter($parameter));
            }
        }

        return $parameterCollection;
    }
}