<?php

namespace BlueDot\Cache;

use BlueDot\Cache\Contracts\CacheInterface;
use BlueDot\Cache\Xml\XmlCache;
use BlueDot\Exception\CacheException;
use BlueDot\Common\ArgumentBag;

class CacheStorage implements CacheInterface
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

        $dom = new \DOMDocument();
        $dom->validateOnParse = true;
        $dom->load($cacheFile);

        self::$instance->cache = new XmlCache($dom, $cacheFile);

        return self::$instance;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return $this->cache->has($name);
    }
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->cache->get($name);
    }
    /**
     * @param string $name
     * @param $value
     */
    public function put(string $name, $value)
    {
        $this->cache->put($name, serialize($value));
    }
    /**
     * @param string $name
     * @return bool
     */
    public function remove(string $name) : bool
    {
        return $this->cache->remove($name);
    }
    /**
     * @param ArgumentBag $statement
     * @return bool
     */
    public function canBeCached(ArgumentBag $statement) : bool
    {
        if ($statement->has('resolved_statement_name')) {
            if ($statement->get('statement_type') === 'select') {
                return true;
            }
        }

        return false;
    }
    /**
     * @param ArgumentBag $statement
     * @return string
     */
    public function createName(ArgumentBag $statement) : string
    {
        $resolvedStatementName = $statement->get('resolved_statement_name');

        if ($statement->has('parameters')) {
            $parameters = $statement->get('parameters');
            $imploded = '';
            foreach ($parameters as $key => $value) {
                $imploded.=$key.'_'.$value;
            }

            $resolvedStatementName.='_'.$imploded;
        }

        return $resolvedStatementName;
    }
}
