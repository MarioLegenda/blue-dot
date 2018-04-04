<?php

namespace BlueDot\Configuration\Filter;

use BlueDot\Entity\FilterableEntityInterface;

class NormalizeJoinedResult implements FilterInterface
{
    /**
     * @var string $linkingColumn
     */
    private $linkingColumn;
    /**
     * @var array $columns
     */
    private $columns;
    /**
     * @var string $methodName
     */
    private $methodName;
    /**
     * NormalizeJoinedResult constructor.
     * @param string $linkingColumn
     * @param array $columns
     * @param string $methodName
     */
    public function __construct(
        string $linkingColumn,
        array $columns,
        string $methodName
    ) {
        $this->linkingColumn = $linkingColumn;
        $this->columns = $columns;
        $this->methodName = $methodName;
    }
    /**
     * @param FilterableEntityInterface $entity
     * @return FilterableEntityInterface
     */
    public function applyFilter(
        FilterableEntityInterface $entity
    ): FilterableEntityInterface {
        return $entity->{$this->methodName}([
            'linking_column' => $this->linkingColumn,
            'columns' => $this->columns,
        ]);
    }
}