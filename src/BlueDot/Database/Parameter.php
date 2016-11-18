<?php

namespace BlueDot\Database;

use BlueDot\Entity\Entity;

class Parameter
{
    /**
     * @var string $key
     */
    private $key;
    /**
     * @var mixed $value
     */
    private $value;
    /**
     * @param array $parameters
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
    /**
     * @return string|string
     */
    public function getKey() : string
    {
        return $this->key;
    }
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Entity $entity
     * @param array $parameters
     * @return Parameter
     */
    public static function entityToParameter(Entity $entity, array $parameters)
    {
        $parameterCollection = new ParameterCollection();

        foreach ($parameters as $parameter) {
            if (!$entity->has($parameter)) {
                return null;
            }

            $parameterCollection->add($parameter, $entity->get($parameter));
        }

        return $parameterCollection;
    }

    public function getType()
    {
        if (is_bool($this->value)) {
            return \PDO::PARAM_BOOL;
        }

        if (is_string($this->value)) {
            return \PDO::PARAM_STR;
        }

        if ($this->value === null) {
            return \PDO::PARAM_NULL;
        }

        if (is_int($this->value)) {
            return \PDO::PARAM_INT;
        }
    }
}