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
use BlueDot\Result\InsertQueryResult;
use BlueDot\Result\MultipleInsertQueryResult;
use BlueDot\Result\SelectQueryResult;

class ScenarioStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * @var ArgumentBag $statements
     */
    private $statements;
    /**
     * @return StrategyInterface
     * @throws BlueDotRuntimeException
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
                if ($statement->has('if_exists')) {
                    if (!$this->statements->has($statement->get('scenario_name').'.'.$statement->get('if_exists'))) {
                        throw new BlueDotRuntimeException(
                            sprintf('Invalid statement. \'if_exists\' statement \'%s\' does not exist in scenario \'%s\'',
                                $statement->get('if_exists'),
                                $statement->get('scenario_name')
                            )
                        );
                    }

                    $ifExistsStatement = $this->statements->get($statement->get('scenario_name').'.'.$statement->get('if_exists'));

                    if ($ifExistsStatement->has('has_to_execute')) {
                        continue;
                    }

                    if (!$this->resultReport->has($ifExistsStatement->get('resolved_statement_name'))) {
                        $recursiveStatementExecution = new RecursiveStatementExecution(
                            $statement,
                            $this->resultReport,
                            $this->connection
                        );

                        $recursiveStatementExecution->execute($this->statements);
                    }
                }

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
                foreach ($this->resultReport as $scenarioName => $report) {
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

        foreach ($returnEntities as $returnEntity) {
            $statementName = $returnEntity->getStatementName();
            $resolvedStatementName = 'scenario.'.$scenarioName.'.'.$statementName;

            if ($this->resultReport->has($resolvedStatementName)) {
                $query = $this->resultReport->get($resolvedStatementName);

                if (!$query instanceof SelectQueryResult) {
                    throw new BlueDotRuntimeException('Return result specified in \'return_entity\' has to be a select sql type for '.$resolvedStatementName);
                }

                if (!$returnEntity->hasColumnName()) {
                    $entity->add($statementName, $query);

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