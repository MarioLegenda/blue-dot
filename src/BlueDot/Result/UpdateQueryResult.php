<?php

namespace BlueDot\Result;

class UpdateQueryResult
{
    /**
     * @var int $rowCount
     */
    private $rowCount = 0;
    /**
     * UpdateQueryResult constructor.
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