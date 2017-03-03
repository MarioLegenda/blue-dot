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
}