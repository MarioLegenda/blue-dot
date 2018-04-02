<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Component\CreateRegularComponent;
use BlueDot\Component\CreateReturnEntitiesComponent;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Execution\LowLevelStrategy\RecursiveStatementExecution;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Result\NullQueryResult;

class ScenarioStrategy implements StrategyInterface
{
    /**
     * @var ScenarioConfiguration $configuration
     */
    private $configuration;
    /**
     * @var Connection $connection
     */
    protected $connection;
    /**
     * @var ArgumentBag $statement
     */
    protected $statement;
    /**
     * @var ArgumentBag $resultReport
     */
    protected $resultReport;
    /**
     * @var ArgumentBag $statements
     */
    private $statements;
    /**
     * ScenarioStrategy constructor.
     * @param ScenarioConfiguration $configuration
     * @param Connection $connection
     */
    public function __construct(
        ScenarioConfiguration $configuration,
        Connection $connection
    ) {
        $this->connection = $connection;
        $this->configuration = $configuration;
    }
    /**
     * @return StrategyInterface
     * @throws BlueDotRuntimeException
     * @throws \BlueDot\Exception\ConnectionException
     */
    public function execute() : StrategyInterface
    {
        $this->connection->connect();

        $rootConfig = $this->statement->get('root_config');

        if ($rootConfig->get('atomic') === true and !$this->connection->getPDO()->inTransaction()) {
            $this->connection->getPDO()->beginTransaction();
        }

        $this->statements = $this->statement->get('statements');

        try {
            foreach ($this->statements as $statement) {
                if ($statement->has('if_exists') or $statement->has('if_not_exists')) {

                    $existsType = ($statement->has('if_exists')) ? 'if_exists' : 'if_not_exists';

                    $existsStatement = $this->statements->get($statement->get('scenario_name').'.'.$statement->get($existsType));

                    if ($existsStatement->has('has_to_execute')) {
                        continue;
                    }

                    if (!$this->resultReport->has($existsStatement->get('resolved_statement_name'))) {
                        $recursiveStatementExecution = new RecursiveStatementExecution(
                            $existsStatement,
                            $this->resultReport,
                            $this->connection
                        );

                        $recursiveStatementExecution->execute($this->statements);

                        unset($recursiveStatementExecution);
                    }

                    $existsResult = $this->resultReport->get($existsStatement->get('resolved_statement_name'));

                    if ($statement->has('if_exists')) {
                        if ($existsResult instanceof NullQueryResult) {
                            continue;
                        }
                    }

                    if ($statement->has('if_not_exists')) {
                        if (!$existsResult instanceof NullQueryResult) {
                            continue;
                        }
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

                unset($recursiveStatementExecution);
            }
        } catch (\PDOException $e) {
            if ($rootConfig->get('atomic') ===  true) {
                $this->handleRollback($rootConfig);
            }

            throw new BlueDotRuntimeException('A PDOException has been thrown for statement '.$statement->get('resolved_statement_name').' with message \''.$e->getMessage().'\'');
        } catch (BlueDotRuntimeException $e) {
            if ($rootConfig->get('atomic') ===  true) {
                $this->handleRollback($rootConfig);
            }

            throw new BlueDotRuntimeException($e->getMessage());
        }

        try {
            if ($rootConfig->get('atomic') === true) {
                $this->connection->getPDO()->commit();
            }
        } catch (\Exception $e) {
            $this->handleRollback($rootConfig);
        }

        return $this;
    }
    /**
     * @return StorageInterface
     */
    public function getResult() : KernelResultInterface
    {
        $scenarioName = $this->statement->get('root_config')->get('scenario_name');

        if ($this->statement->get('root_config')->has('return_data')) {
            if (!$this->resultReport->isEmpty()) {

                $returnData = $this->statement->get('root_config')->get('return_data')->getAllReturnData();

                return (new CreateReturnEntitiesComponent($returnData, $this->resultReport, $scenarioName))->createEntity();
            }
        }

        return (new CreateRegularComponent($this->resultReport))->createEntity();
    }
    /**
     * @param ArgumentBag $rootConfig
     * @throws BlueDotRuntimeException
     */
    private function handleRollback(ArgumentBag $rootConfig)
    {
        if ($rootConfig->get('atomic') === true) {
            if (!$this->connection->getPDO()->inTransaction()) {
                $message = sprintf('Internal error. Scenario %s should be in transaction to rollback but it isn\'t', $rootConfig->get('scenario_name'));
                throw new BlueDotRuntimeException($message);
            }

            $this->connection->getPDO()->rollBack();
        }
    }

}