<?php

namespace BlueDot\Component;

use BlueDot\Common\ArgumentBag;
use BlueDot\Entity\Entity;
use BlueDot\Result\MultipleInsertQueryResult;

class CreateInsertsComponent
{
    /**
     * @var ArgumentBag $report
     */
    private $report;
    /**
     * CreateInsertsComponent constructor.
     * @param ArgumentBag $report
     */
    public function __construct(ArgumentBag $report)
    {
        $this->report = $report;
    }
    /**
     * @return Entity
     */
    public function createEntity() : Entity
    {
        $entity = new Entity();

        foreach ($this->report as $scenarioName => $report) {
            $name = explode('.', $scenarioName)[2];

            if ($report instanceof MultipleInsertQueryResult) {
                $info = new ArgumentBag();

                $info->add('last_insert_id', $report->getLastInsertId());
                $info->add('row_count', $report->getRowCount());

                $entity->add($name, $info);
            }
        }

        return $entity;
    }
}