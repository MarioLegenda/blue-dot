<?php

namespace BlueDot\Result;

use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Filter\FilterInterface;
use BlueDot\Entity\Entity;

class FilterApplier
{
    /**
     * @param Entity $entity
     * @param Filter $filter
     * @return Entity
     */
    public function apply(
        Entity $entity,
        Filter $filter
    ): Entity {
        $filters = $filter->getFilters();
        /** @var Entity $filterProduct */
        $filterProduct = null;

        /** @var FilterInterface $filter */
        foreach ($filters as $filter) {
            if ($filterProduct instanceof Entity) {
                $filterProduct = $filter->applyFilter($filterProduct);

                continue;
            }

            $filterProduct = $filter->applyFilter($entity);
        }

        return $filterProduct;
    }
}