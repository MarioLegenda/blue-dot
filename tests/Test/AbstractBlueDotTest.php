<?php

namespace Test;

use BlueDot\Database\Connection;
use BlueDot\Entity\PromiseInterface;
use BlueDot\BlueDot;
use Test\Model\Language;
use Test\Model\Category;

class AbstractBlueDotTest extends \PHPUnit_Framework_TestCase
{
    protected $blueDot;

    public function setUp()
    {
        $blueDot = new BlueDot(__DIR__.'/config/vocallo_user_db.yml');

        $connection = new Connection();

        $connection
            ->setUser('root')
            ->setPassword('root')
            ->setHost('127.0.0.1')
            ->setDatabaseName('');

        $blueDot
            ->createStatementBuilder($connection)
            ->addSql('DROP DATABASE IF EXISTS langland')
            ->execute();

        $blueDot
            ->createStatementBuilder($connection)
            ->addSql('CREATE DATABASE IF NOT EXISTS langland CHARACTER SET = \'utf8\' COLLATE = \'utf8_general_ci\'')
            ->execute();

        $blueDot->setConfiguration(__DIR__ . '/config/vocallo_user_db.yml');
        $connection
            ->close()
            ->setDatabaseName('langland')
            ->connect();

        $blueDot->setConnection($connection);

        $this->blueDot = $blueDot;
    }
}