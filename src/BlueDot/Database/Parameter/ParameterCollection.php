<?php

namespace BlueDot\Database\Parameter;

use BlueDot\Exception\QueryException;

class ParameterCollection implements \IteratorAggregate, \ArrayAccess
{
    /**
     * @var bool $multiValuedParamsPresent
     */
    private $multiValuedParamsPresent = false;
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
                $this->parameters[] = new Parameter($key, $value);
            }
        }
    }
    /**
     * @return array
     */
    public function getAllValues() : array
    {
        $values = array();
        foreach ($this->parameters as $parameter) {
            $values[] = $parameter->getValue();
        }

        return $values;
    }
    /**
     * @return array
     */
    public function getKeys() : array
    {
        $keys = array();

        foreach ($this->parameters as $parameter) {
            $keys[] = $parameter->getKey();
        }

        return $keys;
    }
    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->parameters);
    }
    /**
     * @param Parameter $parameter
     * @return $this
     */
    public function addParameter(Parameter $parameter) : ParameterCollection
    {
        $this->parameters[] = $parameter;

        return $this;
    }
    /**
     * @param string $key
     * @return bool
     */
    public function hasParameter(string $key) : bool
    {
        foreach ($this->parameters as $parameter) {
            if ($key === $parameter->getKey()) {
                return true;
            }
        }

        return false;
    }
    /**
     * @param string $key
     * @return Parameter
     */
    public function getParameter(string $key) : Parameter
    {
        foreach ($this->parameters as $parameter) {
            if ($key === $parameter->getKey()) {
                return $parameter;
            }
        }
    }
    /**
     * @param array $parameters
     * @return ParameterCollection
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

        $firstCount = null;
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                if ($firstCount === null) {
                    $firstCount = count($value);
                    continue;
                }

                if (count($value) !== $firstCount) {
                    throw new QueryException('When using multi valued parameters for atomic insert, all the parameter keys should have the same number of values');
                }
            }
        }

        return $this;
    }
    /**
     * @param array $parameters
     * @return ParameterCollection
     */
    public function bindValues(array $parameters) : ParameterCollection
    {
        foreach ($parameters as $key => $value) {
            $parameter = $this->getParameter($key);

            if (is_array($value)) {
                $parameter->markMultiValued();
                $this->multiValuedParamsPresent = true;
            }

            $parameter->setValue($value);
        }

        return $this;
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }
    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->parameters[$offset]);
    }
    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return (array_key_exists($offset, $this->parameters)) ? $this->parameters[$offset] : null;
    }
    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->parameters[$offset] = $value;
    }
    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->parameters[$offset]);
    }
}