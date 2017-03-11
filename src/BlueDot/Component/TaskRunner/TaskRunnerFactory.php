<?php

namespace BlueDot\Component\TaskRunner;

class TaskRunnerFactory
{
    public static function createTaskRunner(\Closure $factory) : AbstractTaskRunner
    {
        return $factory->__invoke();
    }
}