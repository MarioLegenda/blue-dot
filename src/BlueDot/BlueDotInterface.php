<?php

namespace BlueDot;

use BlueDot\Common\StorageInterface;

interface BlueDotInterface
{
    /**
     * @param string $name
     * @param array $parameters
     * @return BlueDotInterface
     */
    public function execute(string $name, $parameters = array()) : BlueDotInterface;
    /**
     * @return StorageInterface
     */
    public function getResult() : StorageInterface;
    /**
     * @param \PDO $connection
     * @return BlueDotInterface
     */
    public function setExternalConnection(\PDO $connection) : BlueDotInterface;
}