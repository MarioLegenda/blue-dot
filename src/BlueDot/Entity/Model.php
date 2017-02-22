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
        $this->properties = $properties;
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
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
    /**
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }
}