<?php

namespace BlueDot\Configuration\Flow\Scenario;

class Metadata
{
    /**
     * @var string $resolvedScenarioStatementName
     */
    private $resolvedScenarioStatementName;
    /**
     * @var string $scenarioName
     */
    private $scenarioName;
    /**
     * @var string $singleScenarioName
     */
    private $singleScenarioName;
    /**
     * @var string $sql
     */
    private $sql;
    /**
     * @var string $sqlType
     */
    private $sqlType;
    /**
     * @var bool $canBeEmptyResult
     */
    private $canBeEmptyResult;
    /**
     * @var string|null $ifExistsStatementName
     */
    private $ifExistsStatementName;
    /**
     * @var string|null $ifNotExistsStatementName
     */
    private $ifNotExistsStatementName;
    /**
     * @var array|null $userParameters
     */
    private $userParameters;
    /**
     * @var array|null $configParameters
     */
    private $configParameters;
    /**
     * @var UseOption|null $useOption
     */
    private $useOption;
    /**
     * @var ForeignKey|null
     */
    private $foreignKey;
    /**
     * Metadata constructor.
     * @param string $resolvedScenarioStatementName
     * @param string $sql
     * @param string $sqlType
     * @param bool $canBeEmptyResult
     * @param string|null $ifExistsStatementName
     * @param string|null $ifNotExistsStatementName
     * @param array|null $userParameters
     * @param array|null $configParameters
     * @param UseOption|null $useOption
     * @param ForeignKey|null $foreignKey
     */
    public function __construct(
        string $resolvedScenarioStatementName,
        string $sql,
        string $sqlType,
        bool $canBeEmptyResult,
        string $ifExistsStatementName = null,
        string $ifNotExistsStatementName = null,
        array $userParameters = null,
        array $configParameters = null,
        UseOption $useOption = null,
        ForeignKey $foreignKey = null
    ) {
        $brokenResolvedScenarioName = explode('.', $resolvedScenarioStatementName);

        $this->scenarioName = sprintf('%s.%s', $brokenResolvedScenarioName[0], $brokenResolvedScenarioName[1]);
        $this->singleScenarioName = $brokenResolvedScenarioName[2];
        $this->resolvedScenarioStatementName = $resolvedScenarioStatementName;
        $this->sql = $sql;
        $this->sqlType = $sqlType;
        $this->canBeEmptyResult = $canBeEmptyResult;
        $this->ifExistsStatementName = $ifExistsStatementName;
        $this->ifNotExistsStatementName = $ifNotExistsStatementName;
        $this->userParameters = $userParameters;
        $this->configParameters = $configParameters;
        $this->useOption = $useOption;
        $this->foreignKey = $foreignKey;
    }
    /**
     * @return string
     */
    public function getResolvedScenarioStatementName(): string
    {
        return $this->resolvedScenarioStatementName;
    }
    /**
     * @return string
     */
    public function getScenarioName(): string
    {
        return $this->scenarioName;
    }
    /**
     * @return string
     */
    public function getSingleScenarioName(): string
    {
        return $this->singleScenarioName;
    }
    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }
    /**
     * @return bool
     */
    public function canBeEmptyResult(): bool
    {
        return $this->canBeEmptyResult;
    }
    /**
     * @return string
     */
    public function getIfExistsStatementName(): ?string
    {
        return $this->ifExistsStatementName;
    }
    /**
     * @return string
     */
    public function getIfNotExistsStatementName(): ?string
    {
        return $this->ifNotExistsStatementName;
    }
    /**
     * @return array|null
     */
    public function getUserParameters(): ?array
    {
        return $this->userParameters;
    }
    /**
     * @return array|null
     */
    public function getConfigParameters(): ?array
    {
        return $this->configParameters;
    }
    /**
     * @return UseOption|null
     */
    public function getUseOption(): ?UseOption
    {
        return $this->useOption;
    }
    /**
     * @return ForeignKey|null
     */
    public function getForeignKey(): ?ForeignKey
    {
        return $this->foreignKey;
    }
    /**
     * @return string
     */
    public function getSqlType(): string
    {
        return $this->sqlType;
    }


}