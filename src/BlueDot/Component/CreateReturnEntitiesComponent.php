<?php

namespace BlueDot\Component;


use BlueDot\Common\ArgumentBag;
use BlueDot\Entity\Entity;
use BlueDot\Result\SelectQueryResult;
use BlueDot\Exception\BlueDotRuntimeException;

class CreateReturnEntitiesComponent
{
    /**
     * @var array $returnEntities
     */
    private $returnEntities;
    /**
     * @var ArgumentBag $report
     */
    private $report;
    /**
     * @var string $scenarioName
     */
    private $scenarioName;
    /**
     * CreateReturnEntitiesComponent constructor.
     * @param array $returnEntities
     * @param ArgumentBag $report
     * @param string $scenarioName
     */
    public function __construct(array $returnEntities, ArgumentBag $report, string $scenarioName)
    {
        $this->returnEntities = $returnEntities;
        $this->report = $report;
        $this->scenarioName = $scenarioName;
    }
    /**
     * @return Entity
     * @throws BlueDotRuntimeException
     */
    public function createEntity() : Entity
    {
        $entity = new Entity();

        /**
         *  $return entity is BlueDot\Database\Scenario\ReturnData
         */
        foreach ($this->returnEntities as $returnEntity) {
            $statementName = $returnEntity->getStatementName();
            $resolvedStatementName = 'scenario.'.$this->scenarioName.'.'.$statementName;

            if ($this->report->has($resolvedStatementName)) {
                $query = $this->report->get($resolvedStatementName);

                if (!$query instanceof SelectQueryResult) {
                    throw new BlueDotRuntimeException('Return result specified in \'return_entity\' has to be a select sql type for '.$resolvedStatementName);
                }

                if (!$returnEntity->hasColumnName()) {
                    $entity->add($statementName, $query->getQueryResult());

                    continue;
                }

                $resultEntityColumnName = $returnEntity->getColumnName();

                if (!$query->getMetadata()->hasColumn($resultEntityColumnName)) {
                    if ($returnEntity->hasAlias()) {
                        $alias = $returnEntity->getAlias();

                        $entity->add($alias, null);

                        continue;
                    }

                    $entity->add($resultEntityColumnName, null);

                    continue;
                }

                $resultValue = $query->getColumnValues($resultEntityColumnName);

                if ($returnEntity->hasAlias()) {
                    $alias = $returnEntity->getAlias();

                    $entity->add($alias, $resultValue);

                    continue;
                }

                $entity->add($resultEntityColumnName, $resultValue);
            }
        }

        return $entity;
    }
}