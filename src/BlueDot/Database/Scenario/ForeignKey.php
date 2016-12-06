<?php

namespace BlueDot\Database\Scenario;

class ForeignKey
{
    /**
     * @var string $statementName;
     */
    private $statementName;
    /**
     * @var array $bindTo
     */
    private $bindTo = array();
    /**
     * @param string $statementName
     * @param string $bindTo
     */
    public function __construct(string $statementName, string $bindTo)
    {
        $this->statementName = $statementName;
        $this->bindTo = $bindTo;
    }
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->statementName;
    }
    /**
     * @return string
     */
    public function getBindTo() : string
    {
        return $this->bindTo;
    }
}