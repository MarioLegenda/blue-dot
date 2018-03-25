<?php

namespace Test\Unit;

use BlueDot\Configuration\Cache\CompilerCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class CompilerCacheTest extends TestCase
{
    public function test_compiler_cache()
    {
        $cacheKey = realpath(__DIR__.'/../config/callable_statement_test.yml');

        $compilerCache = new CompilerCache();

        static::assertFalse($compilerCache->isInCache($cacheKey));

        $compilerCache->putInCache($cacheKey, Yaml::parse(file_get_contents($cacheKey)));

        static::assertTrue($compilerCache->isInCache($cacheKey));

        static::assertInternalType('array', $compilerCache->getFromCache($cacheKey));
        static::assertNotEmpty($compilerCache->getFromCache($cacheKey));
    }
}