<?php

namespace BlueDot\Database\Scenario;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Configuration\Scenario\ScenarioConfigurationCollection;
use BlueDot\Database\ParameterCollection;
use BlueDot\Database\ParameterCollectionInterface;
use BlueDot\Exception\QueryException;
use BlueDot\Entity\EntityInterface;

class ScenarioBuilder implements ScenarioInterface
{
    /**
     * @var ArgumentBag $argumentBag
     */
    private $argumentBag;
    /**
     * @param StorageInterface $argumentsBag
     */
    public function __construct(StorageInterface $argumentsBag)
    {
        $this->argumentBag = $argumentsBag;
    }

    public function buildScenario()
    {
        $this->resolveParameters($this->argumentBag);

        return $this->argumentBag;
    }

    private function resolveParameters(StorageInterface $storage)
    {
        $configuration = $storage->get('configuration');
        if ($storage->has('user_parameters')) {
            $parameters = $storage->get('user_parameters');

            if ($configuration instanceof ScenarioConfigurationCollection) {
                $validatedParameters = array();
                foreach ($parameters as $statementName => $parameter) {
                    if (!$configuration->hasScenarioConfiguration($statementName)) {
                        throw new QueryException('You included parameters in your query but not in the configuration for '.$configuration->get('resolved_name'));
                    }

                    $statementConfig = $configuration->getScenarioConfiguration($statementName);

                    $validatedParameters[$statementName] = $this->validateParameters($parameter, $statementConfig);
                }

                $storage->add(
                    'user_parameters',
                    new ParameterCollection($validatedParameters),
                    true
                );

                return;
            }

            $storage->add(
                'user_parameters',
                $this->validateParameters($parameters, $configuration),
                true
            );
        }
    }

    private function validateParameters($parameters, StorageInterface $configuration)
    {
        if (!$parameters instanceof EntityInterface and !is_array($parameters)) {
            throw new QueryException('Invalid argument. If provided, parameters can be an instance of '.EntityInterface::class.', an instance of '.ParameterCollectionInterface::class.' or an array');
        }

        if ($parameters instanceof EntityInterface) {

        }

        $parameters = new ParameterCollection($parameters);
        $configParameters = $configuration->get('parameters');

        if (!empty(array_diff($configParameters, $parameters->getBindingKeys()))) {
            throw new QueryException('Given parameters and parameters in configuration are not equal for '.$configuration->get('resolved_name'));
        }

        return $parameters;
    }
}