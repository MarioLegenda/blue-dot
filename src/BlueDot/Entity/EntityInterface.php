<?php

namespace BlueDot\Entity;

interface EntityInterface
{
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name);
    /**
     * @param string $columnName
     * @param $columnValue
     * @return mixed
     */
    public function set(string $columnName, $columnValue) : EntityInterface;
    /**
     * @return array
     */
    public function toArray() : array;
}