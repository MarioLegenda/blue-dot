<?php

namespace BlueDot\Result;

class MultipleInsertQueryResult
{
    /**
     * @var InsertQueryResult[] $queryResults
     */
    private $queryResults = array();

    /**
     * @param InsertQueryResult $queryResult
     */
    public function addInsertResult(InsertQueryResult $queryResult)
    {
        $this->queryResults[] = $queryResult;
    }
    /**
     * @return array
     */
    public function getInsertedIds() : array
    {
        $insertedIds = array();
        foreach ($this->queryResults as $queryResult) {
            $insertedIds[] = $queryResult->getLastInsertId();
        }

        return $insertedIds;
    }
    /**
     * @return int
     */
    public function getLastInsertId() : int
    {
        return $this->queryResults[count($this->queryResults) - 1]->getLastInsertId();
    }
    /**
     * @return int
     */
    public function getRowCount() : int
    {
        return count($this->queryResults);
    }
}