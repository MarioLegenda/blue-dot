<?php

namespace BlueDot\Database;

use BlueDot\Exception\QueryParameterException;

class ParameterCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array $parameters
     */
    private $parameters = array();
    /**
     * @param mixed $parameters
     */
    public function __construct($parameters = null)
    {
        if ($parameters instanceof ParameterCollection) {
            foreach ($parameters as $parameter) {
                $this->addParameter($parameter);
            }
        }

        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $this->addParameter(new Parameter($key, $value));
            }
        }
    }
    /**
     * @param Parameter $parameter
     * @return ParameterCollection
     */
    public function addParameter(Parameter $parameter) : ParameterCollection
    {
        if ($this->hasParameter($parameter->getKey())) {
            throw new QueryParameterException('Parameter with name'.$parameter->getKey().' already exists');
        }

        $this->parameters[$parameter->getKey()] = $parameter;

        return $this;
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter(string $name)
    {
        if (!$this->hasParameter($name)) {
            return null;
        }

        return $this->parameters[$name];
    }
    /**
     * @param string $name
     * @return bool
     */
    public function hasParameter(string $name) : bool
    {
        return array_key_exists($name, $this->parameters);
    }

    public function isMultipleValueParameter($name) : bool
    {
        if ($this->hasParameter($name)) {
            $parameter = $this->getParameter($name);
            if (is_array($parameter)) {
                return true;
            }

            return false;
        }

        return false;
    }
    /**
     * @return array
     */
    public function getBindingKeys() : array
    {
        if (empty($this->parameters)) {
            return array();
        }

        return array_keys($this->parameters);
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }
    /**
     * @return int
     */
    public function count() : int
    {
        return count($this->parameters);
    }
}