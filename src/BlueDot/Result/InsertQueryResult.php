<?php

namespace BlueDot\Result;

class InsertQueryResult
{
    /**
     * @var int $rowCount
     */
    private $rowCount = 0;
    /**
     * @var null $lastInsertId
     */
    private $lastInsertId = null;
    /**
     * InsertQueryResult constructor.
     * @param $rowCount
     * @param $lastInsertId
     */
    public function __construct($rowCount, $lastInsertId)
    {
        $this->rowCount = $rowCount;
        $this->lastInsertId = $lastInsertId;
    }
    /**
     * @return int
     */
    public function getRowCount() : int
    {
        return $this->rowCount;
    }
    /**
     * @return null
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }
}