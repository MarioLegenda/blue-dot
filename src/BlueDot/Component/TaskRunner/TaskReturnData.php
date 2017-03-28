<?php

namespace BlueDot\Component\TaskRunner;

use BlueDot\Exception\BlueDotRuntimeException;

class TaskReturnData
{
    private $data = array();

    public function add(string $name, $data)
    {
        if ($this->has($name)) {
            throw new BlueDotRuntimeException(
                sprintf('Invalid task. Return data under name %s already exists. This is probably a bug. Please, contact whitepostmail@gmail.com or post an issue on Github', $name)
            );
        }

        $this->data[$name] = $data;
    }

    public function has(string $name) : bool
    {
        return array_key_exists($name, $data);
    }

    public function get(string $name)
    {
        if ($this->has($name)) {
            return $this->data[$name];
        }

        return null;
    }
}