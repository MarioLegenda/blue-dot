<?php

namespace BlueDot\Entity;

use BlueDot\Exception\EntityException;

class ModelCollection
{
    /**
     * @var array $models
     */
    private $models = array();
    /**
     * @param $model
     * @return ModelCollection
     */
    public function addModel($model) : ModelCollection
    {
        $this->models[] = $model;

        return $this;
    }
}