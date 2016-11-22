<?php

namespace BlueDot\Database\Scenario;

class ReturnData
{
    private $isEntireStatemet = false;
    /**
     * @var string $statementName
     */
    private $statementName;
    /**
     * @var string $columnName
     */
    private $columnName;
    /**
     * @var string $alias
     */
    private $alias;
    /**
     * @param string $statementName
     * @param string $columnName
     * @param string|null $alias
     */
    public function __construct(string $statementName, string $columnName = null, string $alias = null)
    {
        if ($columnName === null and $alias === null) {
            $this->isEntireStatemet = true;
        }

        $this->statementName = $statementName;
        $this->columnName = $columnName;
        $this->alias = $alias;
    }
    /**
     * @return string
     */
    public function getStatementName() : string
    {
        return $this->statementName;
    }
    /**
     * @return string|string
     */
    public function getColumnName() : string
    {
        return $this->columnName;
    }
    /**
     * @return bool
     */
    public function hasColumnName() : bool
    {
        return is_string($this->columnName);
    }
    /**
     * @return bool
     */
    public function hasAlias() : bool
    {
        return is_string($this->alias);
    }
    /**
     * @return string
     */
    public function getAlias() : string
    {
        return $this->alias;
    }
    /**
     * @return bool
     */
    public function isEntireStatement() : bool
    {
        return $this->isEntireStatemet;
    }
}