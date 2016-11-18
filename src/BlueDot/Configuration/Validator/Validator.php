<?php

namespace BlueDot\Configuration\Validator;

use BlueDot\Exception\ConfigurationException;

class Validator
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
    public function validate()
    {
        $configuration = new ArrayNode('configuration', $this->configuration);

        $configuration
            ->keyExists('configuration')
            ->stepInto('configuration')
                ->keyExists('connection')
                ->stepInto('connection')
                    ->keyExists('host')->isString('host')
                    ->keyExists('database_name')->isString('database_name')
                    ->keyExists('user')->isString('user')
                    ->keyExists('password')->isString('password')
                ->stepOut()
                ->keyExists('simple')->cannotBeEmpty('simple')
                    ->stepInto('simple')
                        ->isArrayIfExists('select')->isAssociativeStringArray('select')
                        ->isArrayIfExists('insert')->isAssociativeStringArray('insert')
                        ->isArrayIfExists('update')->isAssociativeStringArray('update')
                        ->isArrayIfExists('delete')->isAssociativeStringArray('delete')
                        ->applyToSubelementsOf(array('select', 'insert', 'update', 'delete'), function($nodeName, ArrayNode $node) {
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
                        ->stepOut()
                    ->cannotBeEmpty('scenario')
                    ->isAssociativeStringArray('scenario')
                    ->stepInto('scenario')
                        ->closureValidator('scenario', function($nodeName, ArrayNode $node) {
                            foreach ($node as $key => $value) {
                                $node
                                    ->cannotBeEmpty($key)
                                    ->isAssociativeStringArray($key)
                                    ->stepInto($key)
                                        ->cannotBeEmpty('atomic')
                                        ->isBoolean('atomic')
                                        ->cannotBeEmptyIfExists('return')
                                        ->isArrayIfExists('return')
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
                                                    ->isArrayIfExists('parameters')
                                                    ->cannotBeEmptyIfExists('use')
                                                    ->isArrayIfExists('use')
                                                    ->stepIntoIfExists('use')
                                                        ->cannotBeEmpty('name')->isString('name')
                                                        ->cannotBeEmpty('values')->isArray('values')
                                                    ->stepOut()
                                                    ->isArrayIfExists('foreign_key')
                                                    ->cannotBeEmptyIfExists('foreign_key')
                                                    ->stepIntoIfExists('foreign_key')
                                                        ->cannotBeEmpty('statement_name')->isString('statement_name')
                                                        ->cannotBeEmpty('bind_to')->isAssociativeStringArray('bind_to');
                                            }
                                        });
                            }
                        });
    }
}