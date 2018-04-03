<?php

namespace BlueDot\Configuration\Flow\Simple;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Configuration\Flow\Simple\Enum\SqlTypeFactory;

class Metadata
{
    /**
     * @var string $statementType
     */
    private $statementType;
    /**
     * @var string|TypeInterface $sqlType
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
     * @return string
     */
    public function getStatementType(): string
    {
        return $this->statementType;
    }
    /**
     * @return TypeInterface
     */
    public function getSqlType(): TypeInterface
    {
        if (is_string($this->sqlType)) {
            $this->sqlType = SqlTypeFactory::getType($this->sqlType);
        }

        return $this->sqlType;
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
    public function getResolvedStatementType(): string
    {
        return $this->resolvedStatementType;
    }
    /**
     * @return string
     */
    public function getResolvedStatementName(): string
    {
        return $this->resolvedStatementName;
    }
}