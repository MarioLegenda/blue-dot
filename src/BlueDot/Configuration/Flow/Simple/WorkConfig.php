<?php

namespace BlueDot\Configuration\Flow\Simple;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Configuration\Filter\Filter;
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
     * @param array|null|object $userParameters
     */
    public function injectUserParameters($userParameters)
    {
        $this->userParameters = $userParameters;
    }
    /**
     * @return Filter|null
     */
    public function getFilter(): ?Filter
    {
        return $this->filter;
    }
}
