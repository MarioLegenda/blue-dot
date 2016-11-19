<?php

namespace BlueDot\Database\Parameter;

class Parameter
{
    /**
     * @var bool $multiValued
     */
    private $multiValued = false;
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
     * @void
     */
    public function markMultiValued()
    {
        $this->multiValued = true;
    }
    /**
     * @return bool
     */
    public function isMultiValued() : bool
    {
        return $this->multiValued;
    }

    public function getValuesCount()
    {
        if (!is_array($this->value)) {
            return null;
        }

        return count($this->value);
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
     * @param $inlineType
     * @return int
     */
    public function getType($inlineType = array()) : int
    {
        $value = (!is_array($inlineType)) ? $inlineType : $this->value;

        if (is_string($value)) {
            return \PDO::PARAM_STR;
        }

        if (is_bool($value)) {
            return \PDO::PARAM_BOOL;
        }

        if ($value === null) {
            return \PDO::PARAM_NULL;
        }

        if (is_int($value)) {
            return \PDO::PARAM_INT;
        }

        return \PDO::PARAM_STR;
    }
}