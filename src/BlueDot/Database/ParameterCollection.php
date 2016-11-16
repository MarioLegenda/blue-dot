<?php

namespace BlueDot\Database;

class ParameterCollection implements ParameterCollectionInterface, \IteratorAggregate, \Countable
{
    /**
     * @var array $parameters
     */
    private $parameters = array();
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->add($key, $value);
        }
    }
    /**
     * @param array $parameter
     * @return $this
     */
    public function add(string $name, $value) : ParameterCollectionInterface
    {
        if (is_array($value)) {
            foreach ($value as $val) {
                $this->parameters[$name][] = new Parameter($name, $val);
            }
        }

        if (!is_array($value)) {
            $this->parameters[$name] = new Parameter($name, $value);
        }

        return $this;
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->parameters[$name];
    }
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return array_key_exists($name, $this->parameters);
    }

    public function isMultipleValueParameter($name) : bool
    {
        if ($this->has($name)) {
            $parameter = $this->get($name);

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