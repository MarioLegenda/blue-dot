<?php

namespace BlueDot\Configuration\Flow\Simple;

class Model
{
    /**
     * @var string $class
     */
    private $class;
    /**
     * @var array $properties
     */
    private $properties;
    /**
     * Model constructor.
     * @param string $class
     * @param array $properties
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function __construct(string $class, array $properties)
    {
        $this->class = $class;

        if (!empty($properties)) {
            foreach ($properties as $statement => $property) {
                $this->properties[] = new Property($statement, $property);
            }
        }
    }
    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }
    /**
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }
    /**
     * @param string $column
     * @return Property|null
     */
    public function findPropertyByColumn(string $column)
    {
        if (!is_array($this->properties)) {
            return null;
        }

        foreach ($this->properties as $property) {
            if ($property->getColumn() === $column) {
                return $property;
            }
        }

        return null;
    }
}