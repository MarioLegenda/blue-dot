<?php

namespace BlueDot\Common;

use BlueDot\Common\Util\Util;

abstract class AbstractArgumentBag implements StorageInterface, \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var array $arguments
     */
    protected $arguments = array();
    /**
     * AbstractArgumentBag constructor.
     * @param null|array|StorageInterface $storage
     * @param null|string $name
     * @throws \RuntimeException
     */
    public function __construct($storage = null, $name = null)
    {
        if ($storage !== null) {
            $storageGenerator = ($storage instanceof StorageInterface) ?
                Util::instance()->createGenerator($storage->toArray()) :
                Util::instance()->createGenerator($storage);

            foreach ($storageGenerator as $item) {
                $this->add($item['key'], $item['item']);
            }
        }

        if (is_string($name)) {
            $this->name = $name;
        }
    }
    /**
     * @param string $name
     * @return StorageInterface
     */
    public function setName(string $name) : StorageInterface
    {
        $this->name = $name;

        return $this;
    }
    /**
     * @return string|mixed
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param StorageInterface $storage
     * @param bool|false $overwrite
     * @throws \RuntimeException
     * @return StorageInterface
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
     * @param mixed $value
     * @param bool $overwrite
     * @throws \RuntimeException
     * @return StorageInterface
     */
    public function add(string $name, $value, bool $overwrite = false) : StorageInterface
    {
        if ($this->has($name) and $overwrite === false) {
            throw new \RuntimeException(ArgumentBag::class.' already contains an argument with name '.$name);
        }

        if (is_numeric($value)) {
            $value = (int) $value;
        }

        $this->arguments[$name] = $value;

        return $this;
    }
    /**
     * @param string $name
     * @param mixed $values
     * @return StorageInterface
     * @throws \RuntimeException
     */
    public function addTo(string $name, $values) : StorageInterface
    {
        if (empty($values)) {
            throw new \RuntimeException('Invalid \''.$name.'\'. Cannot add empty array');
        }

        if (!$this->has($name)) {
            $this->createInternalStorage($name);
        }

        $entry = $this->get($name);

        if (!is_array($entry) and !$entry instanceof StorageInterface) {
            throw new \RuntimeException('Cannot add values to storage for \''.$name.'\'. Storage does not contain an iterable data type');
        }

        if (is_array($entry)) {
            throw new \RuntimeException('\''.$name.'\' cannot add values to it. Probably because you used StorageInterface::append() method with it and that cannot be done');
        }

        if (is_array($values)) {
            $mergingStorage = new ArgumentBag($values);
            $entry->mergeStorage($mergingStorage);

            return $this;
        }

        if ($values instanceof StorageInterface) {
            $entry->mergeStorage($values);
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
     * @throws \RuntimeException
     */
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new \RuntimeException(ArgumentBag::class.' does not contain an argument with name '.$name);
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
     * @param string $toRename
     * @param string $newName
     * @return StorageInterface
     * @throws \RuntimeException
     */
    public function rename(string $toRename, string $newName) : StorageInterface
    {
        if (!$this->has($toRename)) {
            throw new \RuntimeException('Cannot rename argument. '.$toRename.' not found');
        }

        $temp = $this->get($toRename);

        unset($this->arguments[$toRename]);

        $this->arguments[$newName] = $temp;

        return $this;
    }
    /**
     * @param string $name
     * @param StorageInterface $storage
     * @return StorageInterface
     * @throws \RuntimeException
     */
    public function appendStorage(string $name, StorageInterface $storage) : StorageInterface
    {
        if ($this->has($name)) {
            $internalStorage = $this->get($name);

            if (!is_array($internalStorage) and !$internalStorage instanceof StorageInterface) {
                throw new \RuntimeException('StorageInterface::append() only supports appending values on traversable data types');
            }
        }

        if (!$this->has($name)) {
            $this->createInternalStorage($name);
        }

        $this->arguments[$name][] = $storage;

        return $this;
    }
    /**
     * @param string $name
     * @param $value
     * @return StorageInterface
     * @throws \RuntimeException
     */
    public function appendValue(string $name, $value) : StorageInterface
    {
        if ($this->has($name)) {
            $internalStorage = $this->get($name);

            if (!is_array($internalStorage) and !$internalStorage instanceof StorageInterface) {
                throw new \RuntimeException('StorageInterface::append() only supports appending values on traversable data types');
            }
        }

        if (!$this->has($name)) {
            $this->createInternalStorage($name);
        }

        $this->arguments[$name][] = $value;

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
    public function toArray() : array
    {
        return $this->arguments;
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
     * @param string $name
     * @throws \RuntimeException
     */
    public function createInternalStorage(string $name)
    {
        if ($this->has($name)) {
            if (!$this->get($name) instanceof StorageInterface) {
                throw new \RuntimeException('Storage for \''.$name.'\' already exists');
            }
        }

        $this->arguments[$name] = new ArgumentBag();
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->arguments);
    }
    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->arguments[$offset]);
    }
    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return (array_key_exists($offset, $this->arguments)) ? $this->arguments[$offset] : null;
    }
    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->arguments[] = $value;
        } else {
            $this->arguments[$offset] = $value;
        }
    }
    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->arguments[$offset]);
    }
    /**
     * @return int
     */
    public function count()
    {
        return count($this->arguments);
    }
}