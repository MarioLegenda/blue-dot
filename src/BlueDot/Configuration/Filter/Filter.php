<?php

namespace BlueDot\Configuration\Filter;

class Filter implements \IteratorAggregate, \Countable
{
    /**
     * @var FilterInterface[] $filters
     */
    private $filters;
    /**
     * Filter constructor.
     * @param FilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }
    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->filters);
    }
    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->filters);
    }
}