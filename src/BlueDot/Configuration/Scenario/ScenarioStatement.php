<?php

namespace BlueDot\Configuration\Scenario;

use BlueDot\Configuration\ConfigurationInterface;

class ScenarioStatement implements ConfigurationInterface
{
    /**
     * @var ForeginKey $foreignKey
     */
    private $foreignKey;
    /**
     * @var UseOption $useOption
     */
    private $useOption;
    /**
     * @var bool $atomic
     */
    private $atomic;
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var string $statement
     */
    private $statement;
    /**
     * @var array $parameters
     */
    private $parameters = array();
    /**
     * @var string $type
     */
    private $type;
    /**
     * @param string $type
     * @param string $name
     * @param string $statement
     * @param array $parameters
     */
    public function __construct(string $type, string $name, string $statement, array $parameters = array())
    {
        $this->type = $type;
        $this->name = $name;
        $this->statement = $statement;
        $this->parameters = $parameters;
    }
    /**
     * @return string|string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return string|string
     */
    public function getStatement() : string
    {
        return $this->statement;
    }
    /**
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
    /**
     * @return string|string
     */
    public function getType() : string
    {
        return $this->type;
    }
    /**
     * @param bool $atomic
     * @return ScenarioStatement
     */
    public function setAtomic(bool $atomic) : ScenarioStatement
    {
        $this->atomic = $atomic;

        return $this;
    }
    /**
     * @return bool
     */
    public function isAtomic() : bool
    {
        return $this->atomic;
    }
    /**
     * @param UseOption $useOption
     * @return $this
     */
    public function setUseOption(UseOption $useOption) : ScenarioStatement
    {
        $this->useOption = $useOption;

        return $this;
    }
    /**
     * @return bool
     */
    public function hasUseOption() : bool
    {
        return $this->useOption instanceof UseOption;
    }
    /**
     * @return UseOption
     */
    public function getUseOption() : UseOption
    {
        return $this->useOption;
    }
    /**
     * @param ForeginKey $foreginKey
     * @return $this
     */
    public function setForeignKey(ForeginKey $foreginKey) : ScenarioStatement
    {
        $this->foreignKey = $foreginKey;

        return $this;
    }
    /**
     * @return bool
     */
    public function hasForeignKey() : bool
    {
        return $this->foreignKey instanceof ForeginKey;
    }
    /**
     * @return ForeginKey
     */
    public function getForeignKey() : ForeginKey
    {
        return $this->foreignKey;
    }
}