<?php

namespace BlueDot\Configuration\Simple;

use BlueDot\Configuration\ConfigurationInterface;
use BlueDot\Exception\ConfigurationException;

class Statement implements ConfigurationInterface
{
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var string $selectStatement
     */
    private $statement;
    /**
     * @var array parameters
     */
    private $parameters;
    /**
     * @param string $name
     * @param string $statement
     * @param array $parameters
     */
    public function __construct(string $name, string $statement, array $parameters = array())
    {
        $this->name = $name;
        $this->statement = $statement;
        $this->parameters = $parameters;

    }
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return string
     */
    public function getStatement() : string
    {
        return $this->statement;
    }
    /**
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
}