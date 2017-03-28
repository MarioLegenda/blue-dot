<?php

namespace BlueDot\Cache\Contracts;

interface CacheInterface
{
    public function has(string $name) : bool;
    public function get(string $name);
    public function put(string $name, $value);
    public function remove(string $name) : bool;
}