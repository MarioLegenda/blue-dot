<?php

namespace BlueDot\Entity;

class Model
{
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var array $properties
     */
    private $properties;
    /**
     * Model constructor.
     * @param string $name
     * @param array $properties
     */
    public function __construct(string $name, array $properties)
    {
        $this->name = $name;

        if (!empty($properties)) {
            foreach ($properties as $statement => $property) {
                $this->properties[] = new Property($statement, $property);
            }
        }
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
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