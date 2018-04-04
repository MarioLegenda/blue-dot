<?php

namespace BlueDot\Configuration\Filter;

use BlueDot\Entity\FilterableEntityInterface;

class ByColumn implements FilterInterface
{
    /**
     * @var string $column
     */
    private $column;
    /**
     * @var string $methodName
     */
    private $methodName;
    /**
     * ByColumn constructor.
     * @param string $column
     * @param string $methodName
     */
    public function __construct(
        string $column,
        string $methodName
    ) {
        $this->column = $column;
        $this->methodName = $methodName;
    }
    /**
     * @inheritdoc
     */
    public function applyFilter(FilterableEntityInterface $entity): FilterableEntityInterface
    {
        return $entity->{$this->methodName}($this->column);
    }
}