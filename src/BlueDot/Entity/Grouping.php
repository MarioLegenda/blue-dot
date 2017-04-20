<?php

namespace BlueDot\Entity;

use BlueDot\Exception\EntityException;

class Grouping
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
     * ArrangeColumns constructor.
     * @param array $grouping
     * @throws EntityException
     */
    public function __construct(array $grouping)
    {
        if (!array_key_exists('linking_column', $grouping)) {
            throw new EntityException(
                sprintf(
                    'Invalid Entity::normalizeJoinedResult() argument. Array argument should contain a linking_column and an array of columns to normalize'
                )
            );
        }

        if (!array_key_exists('columns', $grouping)) {
            throw new EntityException(
                sprintf(
                    'Invalid Entity::normalizeJoinedResult() argument. Array argument should contain a linking_column and an array of columns to normalize'
                )
            );
        }

        if (!is_array($grouping['columns'])) {
            throw new EntityException(
                sprintf(
                    'Invalid Entity::normalizeJoinedResult() argument. Array argument should contain a linking_column and an array of columns to normalize'
                )
            );
        }

        $this->linkingColumn = $grouping['linking_column'];
        $this->columns = $grouping['columns'];
    }
    /**
     * @return string
     */
    public function getLinkingColumn() : string
    {
        return $this->linkingColumn;
    }
    /**
     * @return array
     */
    public function getColumns() : array
    {
        return $this->columns;
    }
}