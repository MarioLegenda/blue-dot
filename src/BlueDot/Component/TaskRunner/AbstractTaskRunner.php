<?php

namespace BlueDot\Component\TaskRunner;

abstract class AbstractTaskRunner
{
    /**
     * @var array $tasks
     */
    protected $tasks = array();
    /**
     * @param TaskInterface $task
     * @return AbstractTaskRunner
     */
    abstract function addTask(TaskInterface $task) : AbstractTaskRunner;
    /**
     * @return mixed
     */
    abstract function doTasks();
}