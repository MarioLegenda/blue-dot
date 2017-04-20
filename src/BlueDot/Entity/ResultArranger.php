<?php

namespace BlueDot\Entity;

use BlueDot\Exception\EntityException;

class ResultArranger
{
    /**
     * @var ArrangeColumns $arrangeColumns
     */
    private $arrangeColumns;
    /**
     * @var array $results
     */
    private $results;
    /**
     * @var array $arranged
     */
    private $arranged = array();
    /**
     * ResultArranger constructor.
     * @param ArrangeColumns $arrangeColumns
     * @param array $results
     */
    public function __construct(array $results, ArrangeColumns $arrangeColumns)
    {
        $this->arrangeColumns = $arrangeColumns;
        $this->results = $results;
    }

    public function arrange(array $results)
    {
        $initialArrange = false;
        $arranged = array();
        foreach ($results as $entry) {
            $columns = $this->arrangeColumns->getColumns();
            $entryKeys = array_keys($entry);

            if ($initialArrange === false) {
                foreach ($entryKeys as $key) {
                    if (in_array($key, $columns) === false) {
                        $arranged[$key] = $entry[$key];
                    }
                }

                $initialArrange = true;
            }

            foreach ($columns as $column) {
                $arranged[$column][] = $entry[$column];
            }
        }

        return $arranged;
    }

    /**
     * @param array $result
     * @return bool
     */
    public function isArranged(array $result) : bool
    {
        $linkingColumn = $this->arrangeColumns->getLinkingColumn();
        $linkingColumnResult = $result[$linkingColumn];

        return in_array($linkingColumnResult, $this->arranged) === true;
    }
}