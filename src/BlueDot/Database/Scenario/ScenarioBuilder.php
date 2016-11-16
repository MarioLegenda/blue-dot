<?php

namespace BlueDot\Database\Scenario;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
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
        if ($this->argumentBag->has('parameters')) {
            $parameters = $this->argumentBag->get('parameters');
            $specificConfiguration = $this->argumentBag->get('specific_configuration');

            $this->argumentBag->add(
                'parameters',
                $this->resolveParameters($parameters, $specificConfiguration),
                true
            );
        }

        $scenario = new Scenario($this->argumentBag);

        return $scenario;
    }

    private function resolveParameters($parameters, $specificConfiguration)
    {
        if (!$parameters instanceof EntityInterface and !is_array($parameters)) {
            throw new QueryException('Invalid argument. If provided, parameters can be an instance of '.EntityInterface::class.', an instance of '.ParameterCollectionInterface::class.' or an array');
        }

        if ($parameters instanceof EntityInterface) {

        }

        $parameters = new ParameterCollection($parameters);
        $configParamters = $specificConfiguration->getParameters();

        if (!empty(array_diff($configParamters, $parameters->getBindingKeys()))) {
            throw new QueryException('Given parameters and parameters in configuration are not equal for '.$specificConfiguration->getType().'.'.$specificConfiguration->getName());
        }

        return $parameters;
    }
}