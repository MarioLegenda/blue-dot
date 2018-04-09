<?php

namespace BlueDot\Configuration\Flow\Simple;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Flow\Enum\MultipleParametersType;
use BlueDot\Configuration\Flow\Enum\SingleParameterType;
use BlueDot\Kernel\Execution\Enum\Parameter\ParameterTypeFactory;

class WorkConfig
{
    /**
     * @var string $sql
     */
    private $sql;
    /**
     * @var Filter $filter
     */
    private $filter;
    /**
     * @var array $configParameters
     */
    private $configParameters;
    /**
     * @var Model $model
     */
    private $model;
    /**
     * @var array $userParameters
     */
    private $userParameters;
    /**
     * @var TypeInterface $userParametersType
     */
    private $userParametersType;
    /**
     * WorkConfig constructor.
     * @param string $sql
     * @param Filter $filter
     * @param array|null $configParameters
     * @param Model|null $model
     */
    public function __construct(
        string $sql,
        Filter $filter = null,
        array $configParameters = null,
        Model $model = null
    ) {
        $this->sql = $sql;
        $this->filter = $filter;

        if (is_null($configParameters)) {
            $configParameters = [];
        }

        $this->configParameters = $configParameters;
        $this->model = $model;
    }
    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }
    /**
     * @return array|null
     */
    public function getConfigParameters(): ?array
    {
        return $this->configParameters;
    }
    /**
     * @return null|array
     */
    public function getUserParameters()
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
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }
    /**
     * @return TypeInterface|null
     */
    public function getUserParametersType(): ?TypeInterface
    {
        return $this->userParametersType;
    }
    /**
     * @param array|null|object $userParameters
     */
    public function injectUserParameters($userParameters)
    {
        $this->userParameters = $userParameters;

        $this->userParametersType = $this->determineParameterType();

    }
    /**
     * @return Filter|null
     */
    public function getFilter(): ?Filter
    {
        return $this->filter;
    }
    /**
     * @return TypeInterface|null
     */
    private function determineParameterType(): ?TypeInterface
    {
        if (empty($this->userParameters)) {
            return null;
        }

        $firstKey = array_keys($this->userParameters)[0];

        if (is_int($firstKey)) {
            $possibleMultipleParameter = $this->userParameters[$firstKey];

            if (is_array($possibleMultipleParameter)) {
                return MultipleParametersType::fromValue();
            }
        }

        if (is_string($firstKey) and !is_array($this->userParameters[$firstKey])) {
            return SingleParameterType::fromValue();
        }

        return SingleParameterType::fromValue();
    }
}
