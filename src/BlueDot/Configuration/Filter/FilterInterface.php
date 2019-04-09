<?php

namespace BlueDot\Configuration\Filter;

use BlueDot\Entity\EntityInterface;
use BlueDot\Entity\FilterableEntityInterface;

interface FilterInterface
{
    /**
     * @param FilterableEntityInterface|EntityInterface $entity
     * @return FilterableEntityInterface
     */
    public function applyFilter(FilterableEntityInterface $entity): FilterableEntityInterface;
}