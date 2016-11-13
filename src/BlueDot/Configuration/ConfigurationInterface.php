<?php

namespace BlueDot\Configuration;

interface ConfigurationInterface
{
    /**
     * @return string
     */
    public function getName() : string;
    /**
     * @return string
     */
    public function getStatement() : string;
    /**
     * @return string
     */
    public function getParameters() : array;
}