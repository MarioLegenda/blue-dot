<?php

namespace BlueDot;

use BlueDot\Common\StorageInterface;
use BlueDot\Entity\PromiseInterface;

interface BlueDotInterface
{
    /**
     * @param string $name
     * @param array $parameters
     * @return PromiseInterface
     */
    public function execute(string $name, $parameters = array()) : PromiseInterface;
    /**
     * @param \PDO $connection
     * @return BlueDotInterface
     */
    public function setExternalConnection(\PDO $connection) : BlueDotInterface;
}