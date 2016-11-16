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
     * @param StorageInterface $storage
     * @param bool|false $overwrite
     * @return mixed
     */
    public function mergeStorage(StorageInterface $storage, bool $overwrite = false);
}