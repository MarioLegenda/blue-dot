<?php

namespace BlueDot\Entity;

use BlueDot\Exception\QueryException;

class EntityCollection implements \IteratorAggregate
{
    /**
     * @var array $collection
     */
    private $collection = array();
    /**
     * @param EntityInterface $result
     * @return $this
     */
    public function add(EntityInterface $result) : EntityCollection
    {
        $this->collection[] = $result;

        return $this;
    }

    /**
     * @param string $columnName
     * @param $columnValue
     * @return array
     * @throws QueryException
     */
    public function findOneBy(string $columnName, $columnValue)
    {
        $foundValues = $this->find($columnName, $columnValue);

        if (empty($foundValues)) {
            return $foundValues;
        }

        if (count($foundValues) !== 1) {
            throw new QueryException('Multiple values found when searching with ResultCollection::findOneBy(). Try using ResultCollection::findBy()');
        }

        return $foundValues[0];
    }
    /**
     * @param string $columnName
     * @param $columnValue
     * @return array
     */
    public function findBy(string $columnName, $columnValue)
    {
        return $this->find($columnName, $columnValue);
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    private function find(string $columnName, $columnValue) : array
    {
        $foundValues = array();
        foreach ($this->collection as $result) {
            if ($result->get($columnName) === $columnValue) {
                $foundValues[] = $result;
            }
        }

        return $foundValues;
    }
}