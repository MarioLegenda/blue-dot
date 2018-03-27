<?php

namespace BlueDot\Database\Model;

use BlueDot\Database\Model\Simple\SimpleConfiguration;

class ConfigurationCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var SimpleConfiguration[] $collection
     */
    protected $collection;
    /**
     * @param string $name
     * @param ConfigurationInterface $configuration
     * @return ConfigurationCollection
     */
    public function add(string $name, ConfigurationInterface $configuration): ConfigurationCollection
    {
        $this->collection[$name] = $configuration;
    }
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->collection);
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->collection);
    }
    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }
}