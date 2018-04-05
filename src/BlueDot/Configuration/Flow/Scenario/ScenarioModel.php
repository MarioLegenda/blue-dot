<?php

namespace BlueDot\Configuration\Flow\Scenario;

class ScenarioModel
{
    /**
     * @var string $class
     */
    private $class;
    /**
     * @var array
     */
    private $binders;
    /**
     * ScenarioModel constructor.
     * @param string $class
     * @param array|null $binders
     */
    public function __construct(
        string $class,
        array $binders = null
    ) {
        $this->class = $class;
        $this->binders = $binders;
    }
    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }
    /**
     * @return bool
     */
    public function hasBinders(): bool
    {
        return is_array($this->binders);
    }
    /**
     * @return array
     */
    public function getBinders(): array
    {
        return $this->binders;
    }
}