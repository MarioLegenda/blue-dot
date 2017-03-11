<?php

namespace BlueDot\Component;

use BlueDot\Common\ArgumentBag;
use BlueDot\Entity\Entity;
use BlueDot\Result\DeleteQueryResult;
use BlueDot\Result\MultipleInsertQueryResult;
use BlueDot\Result\NullQueryResult;
use BlueDot\Result\UpdateQueryResult;

class CreateRegularComponent
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

            if ($report instanceof DeleteQueryResult) {
                $info = new ArgumentBag();

                $info->add('row_count', $report->getRowCount());

                $entity->add($name, $info);
            }

            if ($report instanceof UpdateQueryResult) {
                $info = new ArgumentBag();

                $info->add('row_count', $report->getRowCount());

                $entity->add($name, $info);
            }

            if ($report instanceof NullQueryResult) {
                $info = new ArgumentBag();

                $info->add('row_count', null);

                $entity->add($name, $info);
            }
        }

        return $entity;
    }
}