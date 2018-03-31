<?php

namespace BlueDot\Configuration\Flow\Scenario;

class ForeignKey
{
    /**
     * @var string $statementName;
     */
    private $statementName;
    /**
     * @var string $bindTo
     */
    private $bindTo;
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
    public function getStatementName() : string
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