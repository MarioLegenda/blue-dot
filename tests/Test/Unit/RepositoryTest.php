<?php

namespace Test\Unit;

use BlueDot\Exception\RepositoryException;
use BlueDot\Repository\Repository;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function test_invalid_repository()
    {
        $repository = new Repository();

        $repositoryException = false;
        try {
            $repository->putRepository('invalid');
        } catch (RepositoryException $e) {
            $repositoryException = true;
        }

        static::assertTrue($repositoryException);
    }

    public function test_repository_as_multiple_files()
    {
        $repository = new Repository();

        $repository->putRepository(__DIR__.'/../config/repository/language.yml');

        static::assertEquals(1, count($repository));
        static::assertEquals(1, count($repository->getFiles()));

        static::assertNull($repository->getCurrentlyUsingRepository());
        static::assertEquals(1, count($repository->getWorkingRepositories()));

        $repository->putRepository(__DIR__.'/../config/repository/user.yml');

        static::assertEquals(2, count($repository));
        static::assertEquals(2, count($repository->getFiles()));

        static::assertNull($repository->getCurrentlyUsingRepository());

        $repository->useRepository('language');

        static::assertEquals('language', $repository->getCurrentlyUsingRepository());

        $repository->useRepository('user');

        static::assertEquals('user', $repository->getCurrentlyUsingRepository());

        static::assertTrue($repository->hasRepository('user'));
        static::assertTrue($repository->hasRepository('language'));

        static::assertEquals(2, count($repository->getWorkingRepositories()));
    }
}