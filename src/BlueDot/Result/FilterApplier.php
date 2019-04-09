<?php

namespace BlueDot\Result;

use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Filter\FilterInterface;
use BlueDot\Entity\BaseEntity;

class FilterApplier
{
    /**
     * @param BaseEntity $entity
     * @param Filter $filter
     * @return BaseEntity
     */
    public function apply(
        BaseEntity $entity,
        Filter $filter
    ): BaseEntity {
        $filters = $filter->getFilters();
        /** @var BaseEntity $filterProduct */
        $filterProduct = null;

        /** @var FilterInterface $filter */
        foreach ($filters as $filter) {
            if ($filterProduct instanceof BaseEntity) {
                $filterProduct = $filter->applyFilter($filterProduct);

                continue;
            }

            $filterProduct = $filter->applyFilter($entity);
        }

        return $filterProduct;
    }
}