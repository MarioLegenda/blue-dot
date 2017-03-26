<?php

namespace BlueDot\Cache;

use BlueDot\Cache\Contracts\CacheInterface;
use BlueDot\Cache\Xml\XmlCache;
use BlueDot\Exception\CacheException;
use BlueDot\Common\ArgumentBag;

class CacheStorage
{
    /**
     * @var static Cache $instance
     */
    private static $instance;
    /**
     * @var CacheInterface $cache
     */
    private $cache;
    /**
     * @var HashTable $hashTable
     */
    private $hashTable;
    /**
     * @return CacheStorage
     * @throws CacheException
     */
    public static function getInstance()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $cacheFile = realpath(__DIR__.'/files/cache.xml');

        if (!is_readable($cacheFile)) {
            throw new CacheException(
                sprintf(
                    'Invalid cache file. Cache file %s does not exist or is not readable',
                    $cacheFile
                )
            );
        }

        self::$instance = new self();

        self::$instance->hashTable = new HashTable();

        $dom = new \DOMDocument();
        $dom->validateOnParse = true;
        $dom->load($cacheFile);

        self::$instance->cache = new XmlCache($dom, $cacheFile);

        return self::$instance;
    }

    public function has(ArgumentBag $statement) : bool
    {
        return $this->cache->has($this->hashTable->get($statement));
    }

    public function get(ArgumentBag $statement)
    {
        return $this->cache->get($this->hashTable->get($statement));
    }

    public function put(ArgumentBag $statement, $value)
    {
        $this->cache->put($this->hashTable->get($statement), serialize($value));
    }

    public function remove(ArgumentBag $statement) : bool
    {
        return $this->cache->remove($this->hashTable->get($statement));
    }
    /**
     * @return HashTable
     */
    public function getHashTable() : HashTable
    {
        return $this->hashTable;
    }
}
