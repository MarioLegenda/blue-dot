<?php

namespace BlueDot\Configuration\Filter;

use BlueDot\Entity\FilterableEntityInterface;

class NormalizeIfOneExists implements FilterInterface
{
    /**
     * @var string $methodName
     */
    private $methodName;
    /**
     * FindExact constructor.
     * @param string $methodName
     */
    public function __construct(
        string $methodName
    ) {
        $this->methodName = $methodName;
    }
    /**
     * @inheritdoc
     */
    public function applyFilter(FilterableEntityInterface $entity): FilterableEntityInterface
    {
        return $entity->{$this->methodName}();
    }
}