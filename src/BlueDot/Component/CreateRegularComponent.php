<?php

namespace BlueDot\Component;

use BlueDot\Common\ArgumentBag;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Kernel\TypeConverter;
use BlueDot\Entity\Entity;
use BlueDot\Result\DeleteQueryResult;
use BlueDot\Result\MultipleInsertQueryResult;
use BlueDot\Result\NullQueryResult;
use BlueDot\Result\SelectQueryResult;
use BlueDot\Result\UpdateQueryResult;

class CreateRegularComponent
{
    /**
     * @var KernelResultInterface $results
     */
    private $results;
    /**
     * CreateInsertsComponent constructor.
     * @param KernelResultInterface $results
     */
    public function __construct(KernelResultInterface $results)
    {
        $this->results = $results;
    }
    /**
     * @return Entity
     */
    public function createEntity() : Entity
    {
        $entity = new Entity();
        $typeConverter = new TypeConverter();

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

            if ($report instanceof SelectQueryResult) {
                $info = new Entity($typeConverter->convert($report->getQueryResult()));

                $entity->add($name, $info);
            }
        }

        return $entity;
    }
}