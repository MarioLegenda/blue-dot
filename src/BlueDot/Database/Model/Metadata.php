<?php

namespace BlueDot\Database\Model;

class Metadata
{
    /**
     * @var string $type
     */
    private $type;
    /**
     * @var string $statementType
     */
    private $statementType;
    /**
     * @var string $statementName
     */
    private $statementName;
    /**
     * @var string $resolvedStatementName
     */
    private $resolvedStatementName;
    /**
     * Metadata constructor.
     * @param string $type
     * @param string $statementType
     * @param string $statementName
     * @param string $resolvedStatementName
     */
    public function __construct(
        string $type,
        string $statementType,
        string $statementName,
        string $resolvedStatementName
    ) {
        $this->type = $type;
        $this->statementType = $statementType;
        $this->statementName = $statementName;
        $this->resolvedStatementName = $resolvedStatementName;
    }
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    /**
     * @return string
     */
    public function getStatementType(): string
    {
        return $this->statementType;
    }
    /**
     * @return string
     */
    public function getStatementName(): string
    {
        return $this->statementName;
    }
    /**
     * @return string
     */
    public function getResolvedStatementName(): string
    {
        return $this->resolvedStatementName;
    }
}