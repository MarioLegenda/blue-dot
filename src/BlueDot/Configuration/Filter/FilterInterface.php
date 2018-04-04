<?php

namespace BlueDot\Configuration\Filter;

use BlueDot\Entity\FilterableEntityInterface;

interface FilterInterface
{
    /**
     * @param FilterableEntityInterface $entity
     * @return FilterableEntityInterface
     */
    public function applyFilter(FilterableEntityInterface $entity): FilterableEntityInterface;
}