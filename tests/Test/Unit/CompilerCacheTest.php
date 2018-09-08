<?php

namespace Test\Unit;

use BlueDot\BlueDot;
use PHPUnit\Framework\TestCase;

class CompilerCacheTest extends BaseTest
{
    public function tearDown()
    {
        $cacheDir = __DIR__.'/../../../var/cache/compiler';

        $files = scandir($cacheDir);

        foreach ($files as $file) {
            if ($file !== '.' and $file !== '..') {
                unlink($cacheDir.'/'.$file);
            }
        }
    }

    public function test_compiler_cache()
    {
        $configDir = __DIR__.'/../config/result';

        $blueDot = new BlueDot(
            __DIR__.'/../config/result/prepared_execution_test.yml',
            'prod'
        );

        $blueDot->execute('simple.select.find_all_users');

        $blueDot = new BlueDot(null, 'prod');
        $blueDot->repository()->putRepository($configDir);

        $blueDot->useRepository('prepared_execution_test');
        $blueDot->useRepository('scenario_statement_test');
        $blueDot->useRepository('simple_statement_test');

        static::assertEquals(6, count(scandir($configDir)));
    }
}