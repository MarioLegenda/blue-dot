<?php

namespace BlueDot\Result;

use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Filter\FilterInterface;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityInterface;
use BlueDot\Entity\FilterableEntityInterface;

class FilterApplier
{
    /**
     * @param EntityInterface|FilterableEntityInterface $entity
     * @param Filter $filter
     * @return EntityInterface
     */
    public function apply(
        EntityInterface $entity,
        Filter $filter
    ): EntityInterface {
        $filters = $filter->getFilters();
        /** @var EntityInterface $filterProduct */
        $filterProduct = null;

        /** @var FilterInterface $filter */
        foreach ($filters as $filter) {
            if ($filterProduct instanceof EntityInterface) {
                $filterProduct = $filter->applyFilter($this->makeCopy($filterProduct));

                continue;
            }

            $filterProduct = $filter->applyFilter($this->makeCopy($entity));
        }

        $data = $entity->toArray();
        $productData = $filterProduct->toArray()['data'];
        $data['data'] = $productData;

        return new Entity($entity->getName(), ['data' => $data]);
    }
    /**
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    private function makeCopy(EntityInterface $entity): EntityInterface
    {
        $data = $entity->toArray()['data'];

        return new Entity($entity->getName(), ['data' => $data]);
    }
}