<?php

namespace BlueDot\Common;

use BlueDot\Exception\CommonInternalException;

class ArgumentBag implements StorageInterface, \IteratorAggregate
{
    /**
     * @var array $arguments
     */
    private $arguments = array();
    /**
     * @param StorageInterface $storage
     * @throws CommonInternalException
     */
    public function __construct(StorageInterface $storage = null)
    {
        if ($storage instanceof StorageInterface) {
            foreach ($storage as $key => $item) {
                $this->add($key, $item);
            }
        }
    }
    /**
     * @param StorageInterface $storage
     * @param bool|false $overwrite
     * @throws CommonInternalException
     */
    public function mergeStorage(StorageInterface $storage, bool $overwrite = false)
    {
        if ($storage instanceof StorageInterface) {
            foreach ($storage as $key => $item) {
                $this->add($key, $item);
            }
        }
    }
    /**
     * @param string $name
     * @param $value
     * @param bool $overwrite
     * @throws CommonInternalException
     * @return $this
     */
    public function add(string $name, $value, bool $overwrite = false) : StorageInterface
    {
        if ($this->has($name) and $overwrite === false) {
            throw new CommonInternalException(ArgumentBag::class.' already contains an argument with name '.$name);
        }

        $this->arguments[$name] = $value;

        return $this;
    }
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return array_key_exists($name, $this->arguments);
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new CommonInternalException(ArgumentBag::class.' does not contain an argument with name '.$name);
        }

        return $this->arguments[$name];
    }
    /**
     * @param string $name
     * @return bool
     */
    public function remove(string $name) : bool
    {
        if (!$this->has($name)) {
            return false;
        }

        unset($this->arguments[$name]);

        return true;
    }
    /**
     * @return array
     */
    public function getArgumentKeys() : array
    {
        if (!empty($this->arguments)) {
            return array_keys($this->arguments);
        }

        return array();
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->arguments);
    }
}