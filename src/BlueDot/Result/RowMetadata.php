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
     * @var array $columnNames
     */
    private $columnNames = array();
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

        if (count($data) === 1) {
            $this->isOneRow = true;

            $this->rowCount = 1;
        } else {
            $this->isMultipleRows = true;
            $this->rowCount = count($data);
        }

        $this->extractColumnNames($data);
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
    /**
     * @param string $column
     * @return bool
     */
    public function hasColumn(string $column) : bool
    {
        return in_array($column, $this->columnNames) === true;
    }

    private function extractColumnNames(array $data)
    {
        if ($this->isOneRow()) {
            $this->columnNames = array_keys($data[0]);
        }

        if ($this->isMultipleRows()) {
            $firstRow = $data[0];

            $this->columnNames = array_keys($firstRow);
        }
    }
}