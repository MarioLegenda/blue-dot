<?php

namespace BlueDot;

interface BlueDotInterface
{
    /**
     * @param string $name
     * @param array $parameters
     */
    public function execute(string $name, $parameters = array());
}