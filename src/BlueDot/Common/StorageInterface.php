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
}