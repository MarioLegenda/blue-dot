<?php

namespace BlueDot\Configuration\Flow\Scenario;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Configuration\Flow\Simple\Enum\SqlTypeFactory;
use BlueDot\Configuration\Flow\Simple\Enum\SqlTypes;
use BlueDot\Kernel\Strategy\Enum\IfExistsType;
use BlueDot\Kernel\Strategy\Enum\IfNotExistsType;

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
     * @return bool
     */
    public function hasIfExistsStatement(): bool
    {
        return is_string($this->ifExistsStatementName);
    }
    /**
     * @return bool
     */
    public function hasIfNotExistsStatement(): bool
    {
        return is_string($this->ifNotExistsStatementName);
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
     * @return TypeInterface
     */
    public function getExistsStatementType(): TypeInterface
    {
        if ($this->hasIfExistsStatement()) {
            return IfExistsType::fromValue();
        }

        if ($this->hasIfNotExistsStatement()) {
            return IfNotExistsType::fromValue();
        }
    }
    /**
     * @return string
     */
    public function createExistsFullStatementName(): string
    {
        return sprintf(
            '%s.%s',
            $this->getScenarioName(),
            $this->getExistsStatementName()
        );
    }
    /**
     * @return string
     */
    public function getExistsStatementName(): string
    {
        if ($this->hasIfExistsStatement()) {
            return $this->getIfExistsStatementName();
        }

        if ($this->hasIfNotExistsStatement()) {
            return $this->getIfNotExistsStatementName();
        }
    }
    /**
     * @return array|null
     */
    public function getUserParameters(): array
    {
        if (is_null($this->userParameters) or empty($this->userParameters)) {
            return [];
        }

        if (!is_array($this->userParameters)) {
            return [];
        }

        return $this->userParameters;
    }
    /**
     * @return array|null
     */
    public function getConfigParameters(): array
    {
		if (is_null($this->configParameters)) {
			return [];
		}

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
     * @return string|null
     */
    public function getUseOptionStatementName(): ?string
    {
        if ($this->getUseOption() instanceof UseOption) {
            return sprintf('%s.%s',
                $this->getScenarioName(),
                $this->getUseOption()->getStatementName()
            );
        }
    }
    /**
     * @return ForeignKey|null
     */
    public function getForeignKey(): ?ForeignKey
    {
        return $this->foreignKey;
    }
    /**
     * @return string|null
     */
    public function getForeignKeyStatementName(): ?string
    {
        if ($this->getForeignKey() instanceof ForeignKey) {
            return sprintf('%s.%s',
                $this->getScenarioName(),
                $this->getForeignKey()->getStatementName()
            );
        }
    }
    /**
     * @return TypeInterface
     */
    public function getSqlType(): TypeInterface
    {
        if ($this->sqlType instanceof TypeInterface) {
            return $this->sqlType;
        }

        $sqlTypes = SqlTypes::instance()->toArray();

        if (!array_key_exists($this->sqlType, $sqlTypes)) {
            $this->sqlType = 'other';
        }

        if (is_string($this->sqlType)) {
            $this->sqlType = SqlTypeFactory::getType($this->sqlType);
        }

        return $this->sqlType;
    }
    /**
     * @param array $userParameters
     */
    public function injectUserParameters(array $userParameters)
    {
        $this->userParameters = $userParameters;
    }
    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            "Statement: %s\nSql: %s\ncan_be_empty_result: %s\nif_exists: %s\nif_not_exists: %s\nUser parameters: %s\nConfig parameters: %s\n",
            $this->getResolvedScenarioStatementName(),
            $this->getSql(),
            $this->canBeEmptyResult(),
            $this->getIfExistsStatementName(),
            $this->getIfNotExistsStatementName(),
            implode(', ', array_keys($this->getUserParameters())),
            implode(', ', array_values(($this->getConfigParameters())))
        );
    }
}
