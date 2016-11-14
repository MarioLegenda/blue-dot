<?php

namespace BlueDot;

interface BlueDotInterface
{
    /**
     * @param string $name
     * @param array $parameters
     */
    public function executeSimple(string $name, $parameters = null);
}