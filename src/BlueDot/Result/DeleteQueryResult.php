<?php

namespace BlueDot\Result;

class DeleteQueryResult
{
    /**
     * @var int $rowCount
     */
    private $rowCount;
    /**
     * DeleteResultQuery constructor.
     * @param $rowCount
     */
    public function __construct($rowCount)
    {
        $this->rowCount = $rowCount;
    }
    /**
     * @return int
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }
}