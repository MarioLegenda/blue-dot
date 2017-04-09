<?php

namespace Test\Components;

use BlueDot\BlueDot;
use BlueDot\BlueDotInterface;
use BlueDot\Database\Connection;
use BlueDot\Entity\PromiseInterface;
use Test\Model\Language;

class VocalloConnection implements TestComponentInterface
{
    /**
     * @var $phpunit
     */
    private $phpunit;
    /**
     * @var BlueDotInterface $blueDot
     */
    private $blueDot;
    /**
     * VocalloScenario constructor.
     * @param \PHPUnit_Framework_Assert $phpunit
     * @param BlueDotInterface $blueDot
     */
    public function __construct(\PHPUnit_Framework_Assert $phpunit, BlueDotInterface $blueDot)
    {
        $this->phpunit = $phpunit;
        $this->blueDot = $blueDot;
    }

    /**
     * @void
     */
    public function run()
    {
        $connection = new Connection();

        $connection
            ->setHost('127.0.0.1')
            ->setDatabaseName('langland')
            ->setUser('root')
            ->setPassword('root');

        $blueDot = new BlueDot(null, $connection);

        $this->phpunit->assertInstanceOf(Connection::class, $blueDot->getConnection(), 'BlueDot::getConnection should return a '.Connection::class);

        $blueDot
            ->createStatementBuilder()
            ->addSql('SELECT * FROM languages')
            ->addModel(Language::class)
            ->execute()
            ->success(function(PromiseInterface $promise) {
                $results = $promise->getResult();

                foreach ($results as $result) {
                    $this->phpunit->assertInstanceOf(Language::class, $result, 'Statement builder should have returned an array of '.Language::class.' objects');
                }
            });


    }
}