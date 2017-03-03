<?php

namespace BlueDot\Result;

class RowMetadata
{
    /**
     * @var bool $isOneRow
     */
    private $isOneRow = false;
    /**
     * @var bool $isMultipleRows
     */
    private $isMultipleRows = false;
    /**
     * @var int $rowCount
     */
    private $rowCount = 0;
    /**
     * @var bool $empty
     */
    private $empty = false;
    /**
     * RowMetadata constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (empty($data)) {
            $this->empty = true;

            return;
        }

        if (is_string(array_keys($data)[0])) {
            $this->isOneRow = true;

            $this->rowCount = 1;

            return;
        }

        if (is_int(array_keys($data)[0])) {
            $this->isMultipleRows = true;

            $this->rowCount = count($data);

            return;
        }
    }
    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->empty;
    }
    /**
     * @return bool
     */
    public function isOneRow() : bool
    {
        return $this->isOneRow;
    }
    /**
     * @return bool
     */
    public function isMultipleRows() : bool
    {
        return $this->isMultipleRows;
    }
    /**
     * @return int
     */
    public function getRowCount() : int
    {
        return $this->rowCount;
    }
}