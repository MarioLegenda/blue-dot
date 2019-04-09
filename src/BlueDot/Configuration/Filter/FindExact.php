<?php

namespace BlueDot\Configuration\Filter;

use BlueDot\Entity\BaseEntity;
use BlueDot\Entity\Entity;
use BlueDot\Entity\FilterableEntityInterface;

class FindExact implements FilterInterface
{
    /**
     * @var string $column
     */
    private $column;
    /**
     * @var mixed $value
     */
    private $value;
    /**
     * @var string $methodName
     */
    private $methodName;
    /**
     * FindExact constructor.
     * @param string $column
     * @param mixed $value
     * @param string $methodName
     */
    public function __construct(
        string $column,
        $value,
        string $methodName
    ) {
        $this->column = $column;
        $this->value = $value;
        $this->methodName = $methodName;
    }
    /**
     * @inheritdoc
     */
    public function applyFilter(FilterableEntityInterface $entity): FilterableEntityInterface
    {
        return $entity->{$this->methodName}($this->column, $this->value);
    }
}