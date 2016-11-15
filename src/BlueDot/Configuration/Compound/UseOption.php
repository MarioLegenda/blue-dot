<?php

namespace BlueDot\Configuration\Compound;

class UseOption
{
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var array $values
     */
    private $values;
    /**
     * @param string $name
     * @param array $values
     */
    public function __construct(string $name, array $values)
    {
        $this->name = $name;
        $this->values = $values;
    }
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return array
     */
    public function getValues() : array
    {
        return $this->values;
    }
}