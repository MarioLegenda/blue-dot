<?php

namespace BlueDot;

use BlueDot\Database\Connection;
use BlueDot\Entity\PromiseInterface;
use BlueDot\StatementBuilder\StatementBuilder;

interface BlueDotInterface
{
    /**
     * @param string $name
     * @param array $parameters
     * @return PromiseInterface
     */
    public function execute(string $name, $parameters = array()) : PromiseInterface;
    /**
     * @param Connection $connection
     * @return BlueDotInterface
     */
    public function setConnection(Connection $connection) : BlueDotInterface;
    /**
     * @param Connection|null $connection
     * @return StatementBuilder
     */
    public function createStatementBuilder(Connection $connection = null) : StatementBuilder;
    /**
     * @param string $configSource
     * @return BlueDotInterface
     */
    public function setConfiguration(string $configSource) : BlueDotInterface;
}