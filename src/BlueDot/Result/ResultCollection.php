<?php

namespace BlueDot\Result;

class ResultCollection implements \IteratorAggregate
{
    /**
     * @var array $collection
     */
    private $collection = array();
    /**
     * @param ResultInterface $result
     * @return $this
     */
    public function add(ResultInterface $result) : ResultCollection
    {
        $this->collection[] = $result;

        return $this;
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }
}