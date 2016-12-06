<?php

namespace BlueDot\Database\Parameter;

use BlueDot\Exception\BlueDotRuntimeException;

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
     * @throws BlueDotRuntimeException
     */
    public function compare(array $parameters) : ParameterCollection
    {
        $keys = array_keys($parameters);

        foreach ($keys as $key) {
            if (!$this->hasParameter($key)) {
                throw new BlueDotRuntimeException('Parameter \''.$key.'\' does not exist in the configuration but is provided as a parameter');
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
                    throw new BlueDotRuntimeException('When using multi valued parameters for atomic insert, all the parameter keys should have the same number of values');
                }
            }
        }

        return $this;
    }
    /**
     * @param array $parameters
     * @param bool $isMultiInsert
     * @return mixed
     */
    public function bindValues(array $parameters, bool $isMultiInsert = false)
    {
        foreach ($parameters as $key => $value) {
            $parameter = $this->getParameter($key);

            if (is_array($value)) {
                $parameter->markMultiValued();
                $this->multiValuedParamsPresent = true;
            }

            $parameter->setValue($value);
        }

        if ($isMultiInsert === true) {
            $keys  = array_keys($parameters);

            $valueCount = count($parameters[$keys[0]]);

            $validParameters = array();
            $i = 0;
            while ($i < $valueCount) {
                $parameterCollection = new ParameterCollection();

                foreach ($parameters as $key => $values) {
                    $parameter = new Parameter($key,  $values[$i]);
                    $parameterCollection->addParameter($parameter);
                }

                $validParameters[] = $parameterCollection;

                $i++;
            }

            return $validParameters;
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