<?php

namespace BlueDot\Entity;

class ModelConverter
{
    /**
     * @var Model $model
     */
    private $model;
    /**
     * @var array $result
     */
    private $result;
    /**
     * ModelConverter constructor.
     * @param Model $model
     * @param array $result
     */
    public function __construct(Model $model, array $result)
    {
        $this->model = $model;
        $this->result = $result;
    }
    /**
     * @return mixed
     */
    public function convert()
    {
        if (count($this->result) === 1) {
            return $this->arrayToModel($this->result[0]);
        }

        $modelCollection = array();
        foreach ($this->result as $result) {
            $modelCollection[] = $this->arrayToModel($result);
        }

        return $modelCollection;
    }

    private function arrayToModel(array $result)
    {
        $object = $this->model->getName();

        $object = new $object();

        foreach ($result as $column => $value) {
            $property = $this->findProperty($column);
            $method = 'set'.str_replace('_', '', ucwords($column, '_'));

            if ($property !== false) {
                $method = 'set'.ucfirst($property);
            }

            if (method_exists($object, $method)) {
                $object->{$method}($value);
            }
        }

        return $object;
    }

    private function findProperty(string $column)
    {
        $properties = $this->model->getProperties();

        if (empty($properties)) {
            return false;
        }

        if (!array_key_exists($column, $properties)) {
            return false;
        }

        return $properties[$column];
    }
}