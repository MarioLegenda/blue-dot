<?php

namespace BlueDot\Common;

interface StorageUserInterface
{
    /**
     * @param StorageInterface $storage
     * @param bool $overwrite
     * @return mixed
     */
    public function mergeStorage(StorageInterface $storage, bool $overwrite = false) : StorageInterface;
}