<?php

namespace BlueDot\Common;

interface StorageInterface
{
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool;
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name);
    /**
     * @param string $name
     * @param $value
     * @param bool $overwrite
     * @return mixed
     */
    public function add(string $name, $value, bool $overwrite = false) : StorageInterface;
    /**
     * @param string $name
     * @param mixed $values
     * @return mixed
     */
    public function addTo(string $name, $values) : StorageInterface;
    /**
     * @param string $name
     * @return mixed
     */
    public function remove(string $name) : bool;
    /**
     * @param string $toRename
     * @param string $newName
     * @return mixed
     */
    public function rename(string $toRename, string $newName) : StorageInterface;
    /**
     * @return mixed
     */
    public function isEmpty() : bool;
    /**
     * @param StorageInterface $storage
     * @param bool|false $overwrite
     * @return StorageInterface
     */
    public function mergeStorage(StorageInterface $storage, bool $overwrite = false) : StorageInterface;
    /**
     * @return array
     */
    public function getArgumentKeys() : array;
    /**
     * @return array
     */
    public function toArray() : array;
    /**
     * @param string $name
     * @param StorageInterface $storage
     * @return StorageInterface
     */
    public function appendStorage(string $name, StorageInterface $storage) : StorageInterface;
    /**
     * @param string $name
     * @param mixed $value
     * @return StorageInterface
     */
    public function appendValue(string $name, $value) : StorageInterface;
    /**
     * @param string $name
     * @return StorageInterface
     */
    public function createInternalStorage(string $name);
}