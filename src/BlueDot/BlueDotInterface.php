<?php

namespace BlueDot;

use BlueDot\Database\Connection;
use BlueDot\Entity\PromiseInterface;
use BlueDot\StatementBuilder\StatementBuilder;
use BlueDot\Repository\RepositoryInterface;

interface BlueDotInterface
{
    /**
     * @param string $name
     * @param array|mixed $parameters
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
    /**
     * @return RepositoryInterface
     */
    public function repository() : RepositoryInterface;
    /**
     * @param string $apiName
     * @return BlueDotInterface
     */
    public function useRepository(string $apiName) : BlueDotInterface;
    /**
     * @param string $name
     * @param array $parameters
     * @return BlueDotInterface
     */
    public function prepareExecution(string $name, $parameters = array()) :  BlueDotInterface;
    /**
     * @return array
     */
    public function executePrepared() : array;
}