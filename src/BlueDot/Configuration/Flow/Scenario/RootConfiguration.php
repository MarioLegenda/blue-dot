<?php

namespace BlueDot\Configuration\Flow\Scenario;

class RootConfiguration
{
    /**
     * @var string $scenarioName
     */
    private $scenarioName;
    /**
     * @var bool $atomic
     */
    private $atomic;
    /**
     * @var ScenarioModel $scenarioModel
     */
    private $scenarioModel;
    /**
     * RootConfiguration constructor.
     * @param string $scenarioName
     * @param bool $atomic
     * @param ScenarioModel|null $scenarioModel
     */
    public function __construct(
        string $scenarioName,
        bool $atomic,
        ScenarioModel $scenarioModel = null
    ) {
        $this->scenarioName = $scenarioName;
        $this->atomic = $atomic;
        $this->scenarioModel = $scenarioModel;
    }
    /**
     * @return string
     */
    public function getScenarioName(): string
    {
        return $this->scenarioName;
    }
    /**
     * @return bool
     */
    public function isAtomic(): bool
    {
        return $this->atomic;
    }
    /**
     * @return ScenarioModel|null
     */
    public function getReturnData(): ?ScenarioModel
    {
        return $this->scenarioModel;
    }
}