<?php

namespace BlueDot\Result;

interface ResultInterface
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
    public function set(string $columnName, $columnValue) : ResultInterface;
    /**
     * @return array
     */
    public function toArray() : array;
}