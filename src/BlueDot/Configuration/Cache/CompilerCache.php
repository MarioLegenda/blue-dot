<?php

namespace BlueDot\Configuration\Cache;

use BlueDot\Configuration\Compiler;

class CompilerCache
{
    /**
     * @var string $cacheDir
     */
    private $cacheDir;
    /**
     * @var array $cache
     */
    private $cache = [];
    /**
     * CompilerCache constructor.
     */
    public function __construct()
    {
        $this->cacheDir = realpath(__DIR__.'/../../../../var/cache/compiler/').'/';
    }
    /**
     * @param string $cacheKey
     * @param Compiler $compiler
     */
    public function putInCache(string $cacheKey, Compiler $compiler)
    {
        if (!$this->isInCache($cacheKey)) {
            $normalized = $this->normalize($cacheKey);

            $fileName = sprintf('%s%s', $normalized, '.txt');
            $filePath = sprintf('%s%s', $this->cacheDir, $fileName);

            $handle = fopen($filePath, 'w+');

            fputs($handle, serialize($compiler));

            fclose($handle);

            $this->cache[] = $normalized;
        }
    }
    /**
     * @param string $cacheKey
     * @return Compiler
     * @throws \RuntimeException
     */
    public function getFromCache(string $cacheKey): Compiler
    {
        $normalized = $this->normalize($cacheKey);

        $file = sprintf('%s%s', $this->cacheDir.$normalized, '.txt');

        if (!file_exists($file)) {
            $message = sprintf('File %s does not exist in cache', $file);
            throw new \RuntimeException($message);
        }

        return unserialize(file_get_contents($file));
    }
    /**
     * @param string $cacheKey
     * @return bool
     */
    public function isInCache(string $cacheKey): bool
    {
        return in_array($this->normalize($cacheKey), $this->cache);
    }
    /**
     * @param string $cacheKey
     * @return string
     */
    private function normalize(string $cacheKey): string
    {
        $realPath = realpath($cacheKey);
        $replacedDash = preg_replace('#/#', '_', $realPath);
        $replacedDot = preg_replace('#\.#', '_', $replacedDash);

        return $replacedDot;
    }
}