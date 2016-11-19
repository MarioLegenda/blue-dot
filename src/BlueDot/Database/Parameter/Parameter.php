<?php

namespace BlueDot\Database\Parameter;

class Parameter
{
    /**
     * @var string $key
     */
    private $key;
    /**
     * @var mixed $value
     */
    private $value;
    /**
     * @param string $key
     * @param null $value
     */
    public function __construct(string $key, $value = null)
    {
        $this->key = $key;
        $this->value = $value;
    }
    /**
     * @param string $key
     * @return bool
     */
    public function hasKey(string $key) : bool
    {
        return $this->key === $key;
    }
    /**
     * @return string|string
     */
    public function getKey() : string
    {
        return $this->key;
    }
    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }
    /**
     * @param $value
     * @return Parameter
     */
    public function setValue($value) : Parameter
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getType() : int
    {
        if (is_string($this->value)) {
            return \PDO::PARAM_STR;
        }

        if (is_bool($this->value)) {
            return \PDO::PARAM_BOOL;
        }

        if ($this->value === null) {
            return \PDO::PARAM_NULL;
        }

        if (is_int($this->value)) {
            return \PDO::PARAM_INT;
        }

        return \PDO::PARAM_STR;
    }
}