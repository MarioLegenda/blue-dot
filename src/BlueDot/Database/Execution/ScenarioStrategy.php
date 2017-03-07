<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Component\CreateInsertsComponent;
use BlueDot\Component\CreateReturnEntitiesComponent;
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
                if ($statement->has('if_exists') or $statement->has('if_not_exists')) {
                    $existsType = ($statement->has('if_exists')) ? 'if_exists' : 'if_not_exists';

                    if (!$this->statements->has($statement->get('scenario_name').'.'.$statement->get($existsType))) {
                        throw new BlueDotRuntimeException(
                            sprintf('Invalid statement. \'if_exists\' statement \'%s\' does not exist in scenario \'%s\'',
                                $statement->get('if_exists'),
                                $statement->get('scenario_name')
                            )
                        );
                    }

                    $existsStatement = $this->statements->get($statement->get('scenario_name').'.'.$statement->get('if_exists'));

                    if ($existsStatement->has('has_to_execute')) {
                        continue;
                    }

                    if (!$this->resultReport->has($existsStatement->get('resolved_statement_name'))) {
                        $recursiveStatementExecution = new RecursiveStatementExecution(
                            $statement,
                            $this->resultReport,
                            $this->connection
                        );

                        $recursiveStatementExecution->execute($this->statements);
                    }

                    $existsResult = $this->resultReport->get($existsStatement->get('resolved_statement_name'));
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
    /**
     * @return StorageInterface
     */
    public function getResult() : StorageInterface
    {
        $scenarioName = $this->statement->get('root_config')->get('scenario_name');

        if (!$this->statement->get('root_config')->has('return_data')) {
            if (!$this->resultReport->isEmpty()) {
                return (new CreateInsertsComponent($this->resultReport))->createEntity();
            }
        }

        $returnData = $this->statement->get('root_config')->get('return_data')->getAllReturnData();

        return (new CreateReturnEntitiesComponent($returnData, $this->resultReport, $scenarioName))->createEntity();
    }

}