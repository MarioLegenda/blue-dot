<?php

namespace BlueDot\Entity;

use BlueDot\Configuration\Flow\Simple\Model;
use BlueDot\Configuration\Flow\Simple\Property;

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
     * @return BaseEntity|object
     * @throws \ReflectionException
     */
    public function convertIntoModel()
    {
        if (count($this->result) === 1) {
            return $this->arrayToModel($this->result[0]);
        }

        $modelCollection = array();
        foreach ($this->result as $result) {
            $modelCollection[] = $this->arrayToModel($result);
        }

        return new BaseEntity($modelCollection);
    }
    /**
     * @param array $result
     * @return object
     * @throws \ReflectionException
     */
    private function arrayToModel(array $result)
    {
        $class = $this->model->getClass();

        $object = (new \ReflectionClass($class))->newInstanceWithoutConstructor();

        foreach ($result as $column => $value) {
            $property = $this->findProperty($column);
            $method = 'set'.str_replace('_', '', ucwords($column, '_'));

            if ($property !== false) {
                $method = 'set'.ucfirst($property);
            }

            if (!method_exists($object, $method)) {
                $property = $this->model->findPropertyByColumn($column);

                if ($property instanceof Property) {
                    $modelProperty = $property->getModelProperty();
                    $method = 'set'.ucfirst($modelProperty);

                    $object->{$method}($value);
                }
            }
            else if (method_exists($object, $method)) {
                $object->{$method}($value);
            }
        }

        return $object;
    }
    /**
     * @param string $column
     * @return bool
     */
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