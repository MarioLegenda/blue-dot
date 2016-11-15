<?php

namespace BlueDot\Entity;

class Entity implements EntityInterface
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
     * @param mixed $columnValue
     * @return $this
     */
    public function set(string $columnName, $columnValue) : EntityInterface
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