<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Component\CreateInsertsComponent;
use BlueDot\Component\CreateReturnEntitiesComponent;
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

        if (empty($returnEntities)) {
            if (!$this->resultReport->isEmpty()) {
                return (new CreateInsertsComponent($this->resultReport))->createEntity();
            }
        }

        return (new CreateReturnEntitiesComponent($returnEntities, $this->resultReport, $scenarioName))->createEntity();
    }

}