<?php

namespace BlueDot\Common;

use BlueDot\Exception\CommonInternalException;

abstract class AbstractArgumentBag implements StorageInterface, \IteratorAggregate
{
    protected $arguments = array();
    /**
     * @param StorageInterface $storage
     * @throws CommonInternalException
     */
    public function __construct($storage = null)
    {
        if ($storage !== null) {
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
    public function mergeStorage(StorageInterface $storage, bool $overwrite = false) : StorageInterface
    {
        if ($storage instanceof StorageInterface) {
            foreach ($storage as $key => $item) {
                $this->add($key, $item);
            }
        }

        return $this;
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

    public function addTo(string $name, array $values) : StorageInterface
    {
        if (!$this->has($name)) {
            throw new CommonInternalException('\''.$name.'\' not found. Nothing to add to');
        }

        if (empty($values)) {
            throw new CommonInternalException('Invalid \''.$name.'\'. Cannot add empty array');
        }

        foreach ($values as $key => $value) {
            $this->arguments[$name][$key] = $value;
        }

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

    public function rename(string $toRename, string $newName) : StorageInterface
    {
        if (!$this->has($toRename)) {
            throw new CommonInternalException('Cannot rename argument. '.$toRename.' not found');
        }

        $temp = $this->get($toRename);

        unset($this->arguments[$toRename]);

        $this->arguments[$newName] = $temp;

        return $this;
    }
    /**
     * @param string $name
     * @param StorageInterface $storage
     * @return $this
     * @throws CommonInternalException
     */
    public function append(string $name, StorageInterface $storage) : StorageInterface
    {
        if (!$this->has($name)) {
            $this->arguments[$name] = null;
        }

        $this->arguments[$name][] = $storage;

        return $this;
    }
    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->arguments);
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
     * @return array
     */
    public function toArray() : array
    {
        return $this->arguments;
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->arguments);
    }
}