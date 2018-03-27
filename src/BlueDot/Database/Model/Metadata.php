<?php

namespace BlueDot\Database\Model;

class Metadata implements MetadataInterface
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
     * @var string $resolvedStatementType
     */
    private $resolvedStatementType;
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
        $this->resolvedStatementType = sprintf('%s.%s', $this->type, $this->statementType);
    }
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
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
    public function getStatementName(): string
    {
        return $this->statementName;
    }
    /**
     * @inheritdoc
     */
    public function getResolvedStatementName(): string
    {
        return $this->resolvedStatementName;
    }
    /**
     * @inheritdoc
     */
    public function getResolvedStatementType(): string
    {
        return $this->resolvedStatementType;
    }
}