<?php

namespace BlueDot\Component\TaskRunner;

interface TaskInterface
{
    /**
     * @param string $key
     * @param $argument
     * @return TaskInterface
     */
    public function addArgument(string $key, $argument) : TaskInterface;
    /**
     * @void
     */
    public function doTask();
}