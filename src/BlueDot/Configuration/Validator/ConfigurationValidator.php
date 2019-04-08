<?php

namespace BlueDot\Configuration\Validator;

use BlueDot\Exception\ConfigurationException;

class ConfigurationValidator
{
    /**
     * @var array $configuration
     */
    private $configuration = array();
    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }
    /**
     * @void
     */
    public function validate() : ConfigurationValidator
    {
        $configuration = new ArrayNode('configuration', $this->configuration);

        $simpleConfiguration = $configuration
            ->mandatoryKeyExists('configuration')
            ->stepInto('configuration')
                ->isArrayIfExists('connection')
                ->stepIntoIfExists('connection')
                    ->keyExists('host')->isString('host')
                    ->keyExists('database_name')->isString('database_name')
                    ->keyExists('user')->isString('user')
                    ->keyExists('password')->isString('password')
                    ->isBooleanIfExists('persistent')
                ->stepOut()
                ->isStringIfExists('sql_import');

        $scenarioConfiguration =
            $this->validateSimpleConfiguration($simpleConfiguration)
                    ->cannotBeEmptyIfExists('scenario')
                    ->isArrayIfExists('scenario')
                    ->stepIntoIfExists('scenario');

        $callableConfiguration = $this->validateScenarioConfiguration($scenarioConfiguration);

        $callableConfiguration
            ->cannotBeEmptyIfExists('service')
            ->isArrayIfExists('service')
            ->stepIntoIfExists('service')
                ->closureValidator('service', function($nodeName, ArrayNode $nodes) {
                    foreach ($nodes as $key => $node) {
                        if (!is_string($key)) {
                            throw new ConfigurationException('\''.$key.'\' has to be a string');
                        }

                        $node = new ArrayNode($key, $node);
                        $node
                            ->cannotBeEmpty('class')
                            ->isString('class');
                    }
                });

        return $this;
    }
    /**
     * @return array
     */
    public function getConfiguration() : array
    {
        return $this->configuration;
    }

    private function validateSimpleConfiguration(ArrayNode $node)
    {
        return $node
            ->cannotBeEmptyIfExists('simple')
            ->stepIntoIfExists('simple')
                ->isArrayIfExists('select')
                ->isArrayIfExists('insert')
                ->isArrayIfExists('update')
                ->isArrayIfExists('delete')
                ->isArrayIfExists('other')
                ->applyToSubelementsIfTheyExist(array('select', 'insert', 'update', 'delete', 'other'), function($nodeName, ArrayNode $node) {
                    if ($node->isEmpty()) {
                        throw new ConfigurationException('\''.$nodeName.'\' cannot be empty');
                    }

                    foreach ($node as $key => $nodeValue) {
                        $node->isAssociativeStringArray($key);

                        $nodeValue = new ArrayNode($key, $nodeValue);
                        $nodeValue
                            ->isArrayIfExists('scenario_model')
                            ->stepIntoIfExists('scenario_model')
                                ->cannotBeEmpty('class')
                                ->isString('class')
                                ->cannotBeEmptyIfExists('binders')
                                ->isArrayIfExists('binders')
                            ->cannotBeEmpty('sql')
                            ->isString('sql')
                            ->isArrayIfExists('parameters')
                            ->isBooleanIfExists('cache')
                            ->isArrayIfExists('model')
                            ->cannotBeEmptyIfExists('model')
                            ->stepIntoIfExists('model')
                                ->isString('object')
                                ->isArrayIfExists('properties')
                            ->isArrayIfExists('filter')
                            ->stepIntoIfExists('filter')
                                ->isStringIfExists('by_column')
                                ->isArrayIfExists('find')
                                ->stepIntoIfExists('find')
                                    ->closureValidator('find', function($nodeName, ArrayNode $node) {
                                        $maxValues = 2;
                                        if (count($node) !== $maxValues) {
                                            $message = sprintf(
                                                'Node \'%s\' has to be an array and have %d values',
                                                $nodeName,
                                                $maxValues
                                            );

                                            throw new ConfigurationException($message);
                                        }
                                    })
                                ->stepOut()
                                ->isBooleanIfExists('normalize_if_one_exists')
                                ->isArrayIfExists('normalize_joined_result')
                                ->stepIntoIfExists('normalize_joined_result')
                                    ->cannotBeEmpty('linking_column')
                                    ->isString('linking_column')
                                    ->cannotBeEmpty('columns')
                                    ->isArray('columns');
                    }
                })
            ->stepOut();
    }

    private function validateScenarioConfiguration(ArrayNode $node)
    {
        return $node
            ->closureValidator('scenario', function($nodeName, ArrayNode $node) {
            foreach ($node as $key => $value) {
                $node
                    ->cannotBeEmpty($key)
                    ->isAssociativeStringArray($key)
                    ->stepInto($key)
                    ->cannotBeEmpty('atomic')
                    ->isBoolean('atomic')
                    ->isArrayIfExists('return_data')
                    ->cannotBeEmpty('statements')
                    ->isAssociativeStringArray('statements')
                    ->stepInto('statements')
                    ->closureValidator('statements', function($nodeName, ArrayNode $node) {
                        foreach ($node as $key => $nodeValue) {
                            $node->isAssociativeStringArray($key);

                            $nodeValue = new ArrayNode($key, $nodeValue);

                            $nodeValue
                                ->cannotBeEmpty('sql')
                                ->isString('sql')
                                ->isStringIfExists('if_exists')
                                ->isStringIfExists('if_not_exists')
                                ->isArrayIfExists('parameters')
                                ->isBooleanIfExists('can_be_empty_result')
                                ->cannotBeEmptyIfExists('use')
                                ->isArrayIfExists('use')
                                ->stepIntoIfExists('use')
                                    ->cannotBeEmpty('statement_name')->isString('statement_name')
                                    ->cannotBeEmpty('values')->isArray('values')
                                ->stepOut()
                                    ->isArrayIfExists('foreign_key')
                                    ->cannotBeEmptyIfExists('foreign_key')
                                    ->stepIntoIfExists('foreign_key')
                                    ->cannotBeEmpty('statement_names')->isArray('statement_names')
                                    ->cannotBeEmpty('bind_them_to')->isArray('bind_them_to')
                                ->isArrayIfExists('filter')
                                ->stepIntoIfExists('filter')
                                    ->isStringIfExists('by_column')
                                    ->isArrayIfExists('find')
                                        ->stepIntoIfExists('find')
                                            ->closureValidator('find', function($nodeName, ArrayNode $node) {
                                                $maxValues = 2;
                                                if (count($node) !== $maxValues) {
                                                    $message = sprintf(
                                                'Node \'%s\' has to be an array and have %d values',
                                                      $nodeName,
                                                      $maxValues
                                                    );

                                                    throw new ConfigurationException($message);
                                                }
                                            })
                                        ->stepOut()
                                    ->isBooleanIfExists('normalize_if_one_exists')
                                    ->isArrayIfExists('normalize_joined_result')
                                    ->stepIntoIfExists('normalize_joined_result')
                                        ->cannotBeEmpty('linking_column')
                                        ->isString('linking_column')
                                        ->cannotBeEmpty('columns')
                                        ->isArray('columns');
                        }
                    });
                }
            })
            ->stepOut();
    }
}