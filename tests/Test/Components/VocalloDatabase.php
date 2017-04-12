<?php

namespace Test\Components;

use BlueDot\BlueDot;
use BlueDot\Database\Connection;

class VocalloDatabase extends AbstractTestComponent
{
    public function run()
    {
        $connection = new Connection();

        $connection
            ->setUser('root')
            ->setPassword('root')
            ->setHost('127.0.0.1')
            ->setDatabaseName('');

        $blueDot = new BlueDot();

        $blueDot
            ->createStatementBuilder($connection)
            ->addSql('DROP DATABASE IF EXISTS langland')
            ->execute();

        $blueDot
            ->createStatementBuilder($connection)
            ->addSql('CREATE DATABASE IF NOT EXISTS langland CHARACTER SET = \'utf8\' COLLATE = \'utf8_general_ci\'')
            ->execute();

        $blueDot->setConfiguration(__DIR__ . '/../config/vocallo_user_db.yml');

        $blueDot->execute('scenario.seed');
    }
}