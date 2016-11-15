<?php

namespace BlueDot\Configuration\Simple;

use BlueDot\Configuration\ConfigurationInterface;

class SimpleStatement implements ConfigurationInterface
{
    /**
     * @var string $type
     */
    private $type;
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
     * @param string $type
     */
    public function __construct(string $type, string $name, string $statement, array $parameters = array())
    {
        $this->type = $type;
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
    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
}