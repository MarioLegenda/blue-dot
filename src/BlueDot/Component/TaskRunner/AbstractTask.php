<?php

namespace BlueDot\Component\TaskRunner;

use BlueDot\Exception\ConfigurationException;

abstract class AbstractTask implements TaskInterface
{
    /**
     * @var array $arguments
     */
    protected $arguments = array();
    /**
     * @var array $options
     */
    protected $options = array();
    /**
     * @var mixed $returnData
     */
    protected $returnData = null;
    /**
     * @param string $key
     * @param $argument
     * @return TaskInterface
     */
    public function addArgument(string $key, $argument) : TaskInterface
    {
        $this->arguments[$key] = $argument;

        return $this;
    }
    /**
     * @param string $key
     * @param $options
     * @return TaskInterface
     * @throws ConfigurationException
     */
    public function addOption(string $key, $options) : TaskInterface
    {
        if ($this->hasOption($key)) {
            throw new ConfigurationException(
                sprintf(
                    'Invalid task option. Option \'%s\' already added. This is probably a bug. Please, post an issue on github',
                    $options
                )
            );
        }

        $this->options[$key] = $options;

        return $this;
    }
    /**
     * @param string $key
     * @return bool
     */
    public function hasOption(string $key) : bool
    {
        return array_key_exists($key, $this->options);
    }
    /**
     * @return array
     */
    public function getOptions() : array
    {
        return $this->options;
    }
    /**
     * @param array $options
     * @return TaskInterface
     */
    public function setOptions(array $options) : TaskInterface
    {
        if (empty($options)) {
            return $this;
        }

        foreach ($options as $key => $option) {
            $this->addOption($key, $option);
        }

        return $this;
    }
}