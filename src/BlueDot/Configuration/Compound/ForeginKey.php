<?php

namespace BlueDot\Configuration\Compound;

class ForeginKey
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
     * @param array $bindTo
     */
    public function __construct(string $statementName, array $bindTo)
    {
        $this->statementName = $statementName;
        $this->bindTo = $bindTo;
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
    public function getBindTo() : array
    {
        return $this->bindTo;
    }
}