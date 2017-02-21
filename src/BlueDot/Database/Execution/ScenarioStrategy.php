<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Database\Execution\LowLevelStrategy\BasicStatementExecution;
use BlueDot\Database\Parameter\ParameterCollection;
use BlueDot\Database\Parameter\Parameter;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityCollection;
use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Database\Execution\LowLevelStrategy\RecursiveStatementExecution;

class ScenarioStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * @var ArgumentBag $statements
     */
    private $statements;
    /**
     * @return StrategyInterface
     */
    public function execute() : StrategyInterface
    {
        $this->connection->connect();

        $rootConfig = $this->statement->get('root_config');

        if ($rootConfig->get('atomic') === true) {
            $this->connection->getConnection()->beginTransaction();
        }

        $this->statements = $this->statement->get('statements');

        foreach ($this->statements as $statement) {
            try {
                if ($statement->has('has_to_execute')) {
                    continue;
                }

                if ($this->resultReport->has($statement->get('resolved_statement_name'))) {
                    continue;
                }

                $recursiveStatementExecution = new RecursiveStatementExecution(
                    $statement,
                    $this->resultReport,
                    $this->connection
                );

                $recursiveStatementExecution->execute($this->statements);

            } catch (\PDOException $e) {
                throw new BlueDotRuntimeException('A PDOException has been thrown for statement '.$statement->get('resolved_statement_name').' with message \''.$e->getMessage().'\'');
            }
        }

        if ($rootConfig->get('atomic') === true) {
            // make inTransaction() check here
            $this->connection->getConnection()->commit();
        }

        return $this;
    }

    public function getResult() : StorageInterface
    {
        $returnEntities = $this->statement->get('root_config')->get('return_entity')->getAllReturnData();
        $scenarioName = $this->statement->get('root_config')->get('scenario_name');

        $entity = new Entity();

        if (empty($returnEntities)) {
            if (!$this->resultReport->isEmpty()) {
                var_dump($this->resultReport);
                foreach ($this->resultReport as $scenarioName => $report) {
                    $name = explode('.', $scenarioName)[2];

                    if (!$report->isEmpty()) {
                        $info = new ArgumentBag();

                        if ($report->has('last_insert_id')) {
                            $info->add('last_insert_id', $report->get('last_insert_id'));
                        }

                        $info->add('row_count', $report->get('row_count'));

                        $entity->add($name, $info);
                    }
                }

                return $entity;
            }
        }

        foreach ($returnEntities as $returnEntity) {
            $statementName = $returnEntity->getStatementName();
            $resolvedStatementName = 'scenario.'.$scenarioName.'.'.$statementName;

            if ($this->resultReport->has($resolvedStatementName)) {
                $resultEntity = $this->resultReport->get($resolvedStatementName);

                if (!$resultEntity instanceof $resultEntity) {
                    throw new BlueDotRuntimeException('Return result specified in \'return_entity\' has to be a select sql type for '.$resolvedStatementName);
                }

                if (!$returnEntity->hasColumnName()) {
                    $entity->add($statementName, $resultEntity);

                    continue;
                }

                $resultEntityColumnName = $returnEntity->getColumnName();
                $resultValue = $resultEntity->get($resultEntityColumnName);

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