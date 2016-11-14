<?php

namespace BlueDot\Database;

class ParameterCollection implements ParameterCollectionInterface, \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var array $parameters
     */
    private $parameters = array();
    /**
     * @param array $parameter
     * @return $this
     */
    public function add(string $name, $value) : ParameterCollectionInterface
    {
        $this->parameters[] = new Parameters(array($name => $value));

        return $this;
    }
    /**
     * @return array
     */
    public function getBindingKeys() : array
    {
        if (empty($this->parameters)) {
            return array();
        }

        return array_keys($this->parameters[0]->getParameters());
    }
    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->parameters);
    }
    /**
     * @param mixed $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->parameters[$offset];
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
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $params = array();

        foreach ($this->parameters as $parameter) {
            $params[] = $parameter->getParameters();
        }

        return new \ArrayIterator($params);
    }
    /**
     * @return int
     */
    public function count() : int
    {
        return count($this->parameters);
    }
    /**
     * @return array
     */
    public function toArray() : array
    {
        if (count($this->parameters) === 1) {
            return $this->parameters[0];
        }

        $params = array();
        foreach ($this->parameters as $param) {
            $params[] = $param->getParameters();
        }

        return $params;
    }
}