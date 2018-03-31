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
     * @var ReturnData $returnData
     */
    private $returnData;

    public function __construct(
        string $scenarioName,
        bool $atomic,
        ReturnData $returnData = null
    ) {
        $this->scenarioName = $scenarioName;
        $this->atomic = $atomic;
        $this->returnData;
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
     * @return ReturnData|null
     */
    public function getReturnData(): ?ReturnData
    {
        return $this->returnData;
    }
}