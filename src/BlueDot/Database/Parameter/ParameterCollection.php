<?php

namespace BlueDot\Database\Parameter;

use BlueDot\Exception\CommonInternalException;
use BlueDot\Exception\ConfigurationException;
use BlueDot\Exception\QueryException;

class ParameterCollection
{
    /**
     * @var array $parameters
     */
    private $parameters;
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        if (!empty($parameters)) {
            foreach ($parameters as $key => $value) {
                $this->parameters[$key] = new Parameter($key, $value);
            }
        }
    }
    /**
     * @param Parameter $parameter
     * @return $this
     */
    public function addParameter(Parameter $parameter) : ParameterCollection
    {
        $this->parameters[$parameter->getKey()] = $parameter;

        return $this;
    }
    /**
     * @param string $key
     * @return bool
     */
    public function hasParameter(string $key) : bool
    {
        return array_key_exists($key, $this->parameters);
    }

    public function getParameter(string $key) : Parameter
    {
        return $this->parameters[$key];
    }
    /**
     * @param array $parameters
     * @return $this
     * @throws QueryException
     */
    public function compare(array $parameters) : ParameterCollection
    {
        $keys = array_keys($parameters);

        foreach ($keys as $key) {
            if (!$this->hasParameter($key)) {
                throw new QueryException('Parameter \''.$key.'\' does not exist in the configuration but is provided as a parameter');
            }
        }

        return $this;
    }

    public function bindValues(array $parameters) : ParameterCollection
    {
        foreach ($parameters as $key => $value) {
            $parameter = $this->getParameter($key);

            $parameter->setValue($value);
        }

        return $this;
    }
}