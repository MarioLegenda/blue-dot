<?php

namespace BlueDot\Database\Model\Simple;

use BlueDot\Database\Model\Model;

class WorkConfig
{
    /**
     * @var string $sql
     */
    private $sql;
    /**
     * @var array $configParameters
     */
    private $configParameters;
    /**
     * @var Model $model
     */
    private $model;
    /**
     * WorkConfig constructor.
     * @param string $sql
     * @param array|null $configParameters
     * @param Model|null $model
     */
    public function __construct(
        string $sql,
        array $configParameters = null,
        Model $model = null
    ) {
        $this->sql = $sql;
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
     * @return array
     */
    public function getConfigParameters(): array
    {
        return $this->configParameters;
    }
    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }
}