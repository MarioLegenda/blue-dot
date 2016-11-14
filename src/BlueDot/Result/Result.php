<?php

namespace BlueDot\Result;

class Result implements ResultInterface
{
    /**
     * @var array $values
     */
    private $values = array();
    /**
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        $this->values = $values;
    }
    /**
     * @param string $name
     * @return null
     */
    public function get(string $name)
    {
        if (array_key_exists($name, $this->values)) {
            return $this->values[$name];
        }

        return null;
    }
    /**
     * @param string $columnName
     * @param $columnValue
     * @return $this
     */
    public function set(string $columnName, $columnValue) : ResultInterface
    {
        $this->values[$columnName] = $columnValue;

        return $this;
    }
    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->values;
    }
}