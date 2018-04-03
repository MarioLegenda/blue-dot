<?php

namespace Test\Unit;

use BlueDot\Repository\Repository;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    /**
     * @var array $dirs
     */
    private $dirs = [];
    /**
     * @var array $files
     */
    private $files = [];

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();

        $this->dirs = [
            __DIR__.'/../config/compiler',
            __DIR__.'/../config/result',
        ];

        $this->files = [
            __DIR__.'/../config/compiler/connection_test.yml',
            __DIR__.'/../config/compiler/scenario_statement_test.yml',
            __DIR__.'/../config/compiler/simple_statement_test.yml',
            __DIR__.'/../config/compiler/service_statement_test.yml',
            __DIR__.'/../config/result/scenario_statement_test.yml',
            __DIR__.'/../config/result/simple_statement_test.yml',
            __DIR__.'/../config/result/prepared_execution_test.yml',
            'invalid_directory'
        ];
    }

    public function test_repository_exceptions()
    {
        $repository = new Repository();

        $entersDuplicateRepositoryException = false;
        try {
            $repository->putRepository($this->files[1]);
            $repository->putRepository($this->files[4]);
        } catch (\RuntimeException $e) {
            $entersDuplicateRepositoryException = true;
        }

        static::assertTrue($entersDuplicateRepositoryException);

        $entersInvalidFile = false;
        try {
            $repository->putRepository($this->files[7]);
        } catch (\RuntimeException $e) {
            $entersInvalidFile = true;
        }

        static::assertTrue($entersInvalidFile);
    }

    public function test_repository()
    {
        $repository = new Repository();

        $repository->putRepository($this->dirs[0]);

        static::assertNull($repository->getCurrentlyUsingRepository());
        static::assertNotEmpty($repository->getWorkingRepositories());
        static::assertInternalType('array', $repository->getWorkingRepositories());

        $repository->useRepository('connection_test');

        static::assertEquals('connection_test', $repository->getCurrentlyUsingRepository());

        $repository->useRepository('scenario_statement_test');

        static::assertEquals('scenario_statement_test', $repository->getCurrentlyUsingRepository());
    }
}