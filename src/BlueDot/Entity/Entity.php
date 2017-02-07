<?php

namespace BlueDot\Entity;

use BlueDot\Common\AbstractArgumentBag;
use BlueDot\Exception\EntityException;

class Entity extends AbstractArgumentBag
{
    /**
     * @param array $findBy
     * @throws EntityException
     * @returns mixed
     */
    public function findBy(array $findBy)
    {
        $keys = array_keys($findBy);

        foreach ($keys as $key) {
            if (!is_string($key)) {
                throw new EntityException(sprintf(
                    'Invalid argument for Entity::findBy(). Argument should be a association array, not numeric array'
                ));
            }
        }

        foreach ($this->arguments as $argument) {
            $result = array_intersect_assoc($findBy, $argument);

            if (!empty($result) and count($findBy) === count($result)) {
                return new Entity(array($argument));
            }
        }

        return null;
    }
    /**
     * @param string $column
     * @param string $value
     * @return mixed
     */
    public function find(string $column, string $value)
    {
        return $this->findBy(array(
            $column => $value,
        ));
    }
    /**
     * @param string $column
     * @return array|null
     */
    public function extract(string $column)
    {
        $columns = array();
        foreach ($this->arguments as $argument) {
            if (array_key_exists($column, $argument)) {
                $columns[$column][] = $argument[$column];
            }
        }

        if (empty($columns)) {
            return null;
        }

        return $columns;
    }
}