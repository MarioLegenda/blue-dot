<?php

namespace BlueDot\Result;

class Result
{
    /**
     * @var array $values
     */
    private $values = array();
    /**
     * @param array $values
     */
    public function __construct(array $values)
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
}