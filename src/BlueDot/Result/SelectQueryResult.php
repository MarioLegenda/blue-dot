<?php

namespace BlueDot\Result;

class SelectQueryResult
{
    /**
     * @var array $queryResult
     */
    private $queryResult;
    /**
     * @var RowMetadata $metadata
     */
    private $metadata;
    /**
     * SelectQueryResult constructor.
     * @param array $queryResult
     * @param RowMetadata $metadata
     */
    public function __construct(array $queryResult, RowMetadata $metadata)
    {
        $this->queryResult = $queryResult;
        $this->metadata = $metadata;
    }
    /**
     * @return RowMetadata
     */
    public function getMetadata() : RowMetadata
    {
        return $this->metadata;
    }
    /**
     * @return array
     */
    public function getQueryResult() : array
    {
        return $this->queryResult;
    }
    /**
     * @param string $column
     * @return array|mixed|null
     */
    public function getColumnValues(string $column)
    {
        if (!$this->getMetadata()->hasColumn($column)) {
            return null;
        }

        if ($this->getMetadata()->isOneRow()) {
            return $this->queryResult[0][$column];
        }

        if ($this->getMetadata()->isMultipleRows()) {
            $results = array();
            foreach ($this->queryResult as $result) {
                $results[] = $result[$column];
            }

            return $results;
        }
    }
}