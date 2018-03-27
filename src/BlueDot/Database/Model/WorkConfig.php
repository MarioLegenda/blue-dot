<?php

namespace BlueDot\Database\Model;

class WorkConfig implements WorkConfigInterface
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
     * @var array $userParameters
     */
    private $userParameters;
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

        if (is_null($configParameters)) {
            $configParameters = [];
        }

        $this->configParameters = $configParameters;
        $this->model = $model;
    }
    /**
     * @inheritdoc
     */
    public function getSql(): string
    {
        return $this->sql;
    }
    /**
     * @inheritdoc
     */
    public function getConfigParameters(): ?array
    {
        return $this->configParameters;
    }
    /**
     * @inheritdoc
     */
    public function getUserParameters(): array
    {
        return $this->userParameters;
    }
    /**
     * @inheritdoc
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }
    /**
     * @inheritdoc
     */
    public function injectUserParameters(array $userParameters)
    {
        $this->userParameters = $userParameters;
    }
}