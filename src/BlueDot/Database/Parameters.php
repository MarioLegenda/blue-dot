<?php

namespace BlueDot\Database;

class Parameters
{
    /**
     * @var array $parameters
     */
    private $parameters = array();
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }
    /**
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
}