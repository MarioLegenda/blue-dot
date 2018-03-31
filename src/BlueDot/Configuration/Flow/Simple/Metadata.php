<?php

namespace BlueDot\Configuration\Flow\Simple;

class Metadata implements MetadataInterface
{
    /**
     * @var string $statementType
     */
    private $statementType;
    /**
     * @var string $sqlType
     */
    private $sqlType;
    /**
     * @var string $statementName
     */
    private $statementName;
    /**
     * @var string $resolvedStatementType
     */
    private $resolvedStatementType;
    /**
     * @var string $resolvedStatementName
     */
    private $resolvedStatementName;
    /**
     * Metadata constructor.
     * @param string $statementType
     * @param string $sqlType
     * @param string $statementName
     * @param string $resolvedStatementType
     * @param string $resolvedStatementName
     */
    public function __construct(
        string $statementType,
        string $sqlType,
        string $statementName,
        string $resolvedStatementType,
        string $resolvedStatementName
    ) {
        $this->statementType = $statementType;
        $this->sqlType = $sqlType;
        $this->statementName = $statementName;
        $this->resolvedStatementType = $resolvedStatementType;
        $this->resolvedStatementName = $resolvedStatementName;
    }
    /**
     * @inheritdoc
     */
    public function getStatementType(): string
    {
        return $this->statementType;
    }
    /**
     * @inheritdoc
     */
    public function getSqlType(): string
    {
        return $this->sqlType;
    }
    /**
     * @inheritdoc
     */
    public function getStatementName(): string
    {
        return $this->statementName;
    }
    /**
     * @inheritdoc
     */
    public function getResolvedStatementType(): string
    {
        return $this->resolvedStatementType;
    }
    /**
     * @inheritdoc
     */
    public function getResolvedStatementName(): string
    {
        return $this->resolvedStatementName;
    }
}