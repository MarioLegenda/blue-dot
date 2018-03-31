<?php

namespace BlueDot\Configuration\Flow\Scenario;

class UseOption
{
    /**
     * @var string $statementName
     */
    private $statementName;
    /**
     * @var array $values
     */
    private $values;
    /**
     * @param string $name
     * @param array $values
     */
    public function __construct(string $name, array $values)
    {
        $this->statementName = $name;
        $this->values = $values;
    }
    /**
     * @return string
     */
    public function getStatementName() : string
    {
        return $this->statementName;
    }
    /**
     * @return array
     */
    public function getValues() : array
    {
        return $this->values;
    }
}