<?php

namespace BlueDot\Configuration\Validator;

use BlueDot\Exception\ConfigurationException;
use BlueDot\Configuration\Validator\ArrayNode;

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
            ->keyExists('configuration')
            ->stepInto('configuration')
                ->keyExists('connection')
                ->stepInto('connection')
                    ->keyExists('host')->isString('host')
                    ->keyExists('database_name')->isString('database_name')
                    ->keyExists('user')->isString('user')
                    ->keyExists('password')->isString('password')
                ->stepOut();

        $scenarioConfiguration =
            $this->validateSimpleConfiguration($simpleConfiguration)
                    ->cannotBeEmptyIfExists('scenario')
                    ->isArrayIfExists('scenario')
                    ->stepIntoIfExists('scenario');

        $callableConfiguration = $this->validateScenarioConfiguration($scenarioConfiguration);

        $callableConfiguration
            ->cannotBeEmptyIfExists('callable')
            ->isArrayIfExists('callable')
            ->stepIntoIfExists('callable')
                ->closureValidator('callable', function($nodeName, ArrayNode $nodes) {
                    foreach ($nodes as $key => $node) {
                        if (!is_string($key)) {
                            throw new ConfigurationException('\''.$key.'\' has to be a string');
                        }

                        $node = new ArrayNode($key, $node);
                        $node
                            ->cannotBeEmpty('type')
                            ->isString('type')
                            ->hasToBeOneOf('type', array('object', 'service'))
                            ->cannotBeEmpty('name')
                            ->isString('name');
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
                ->applyToSubelementsIfTheyExist(array('select', 'insert', 'update', 'delete'), function($nodeName, ArrayNode $node) {
                    if ($node->isEmpty()) {
                        throw new ConfigurationException('\''.$nodeName.'\' cannot be empty');
                    }

                    foreach ($node as $key => $nodeValue) {
                        $node->isAssociativeStringArray($key);

                        $nodeValue = new ArrayNode($key, $nodeValue);
                        $nodeValue
                            ->cannotBeEmpty('sql')
                            ->isString('sql')
                            ->isArrayIfExists('parameters');
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
                    ->isArray('return_entity')
                    ->isArrayIfExists('rules')
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
                                ->cannotBeEmpty('sql_type')
                                ->isString('sql_type')
                                ->hasToBeOneOf('sql_type', array('select', 'insert', 'update', 'delete', 'database', 'table'))
                                ->isArrayIfExists('parameters')
                                ->cannotBeEmptyIfExists('use')
                                ->isArrayIfExists('use')
                                ->stepIntoIfExists('use')
                                    ->cannotBeEmpty('statement_name')->isString('statement_name')
                                    ->cannotBeEmpty('values')->isArray('values')
                                ->stepOut()
                                    ->isArrayIfExists('foreign_key')
                                    ->cannotBeEmptyIfExists('foreign_key')
                                    ->stepIntoIfExists('foreign_key')
                                    ->cannotBeEmpty('statement_name')->isString('statement_name')
                                    ->cannotBeEmpty('bind_to')->isString('bind_to');
                        }
                    });
                }
            })
            ->stepOut();
    }
}