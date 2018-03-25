<?php

namespace BlueDot\Database\Execution\LowLevelStrategy;

use BlueDot\Database\Execution\StrategyInterface;
use BlueDot\Database\Connection;
use BlueDot\Common\ArgumentBag;

use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Database\Parameter\Parameter;
use BlueDot\Result\InsertQueryResult;
use BlueDot\Result\MultipleInsertQueryResult;
use BlueDot\Result\ResultReportContext;
use BlueDot\Result\SelectQueryResult;

class RecursiveStatementExecution implements StrategyInterface
{
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * @var ArgumentBag $resultReport
     */
    private $resultReport;

    public function __construct(
        ArgumentBag $statement,
        ArgumentBag $resultReport,
        Connection $connection
    )
    {
        $this->statement = $statement;
        $this->connection = $connection;
        $this->resultReport = $resultReport;
    }
    /**
     * @param ArgumentBag $statements
     * @return StrategyInterface
     * @throws BlueDotRuntimeException
     */
    public function execute(ArgumentBag $statements = null) : StrategyInterface
    {
/*        if ($this->statement->get('statement_type') === 'database' or $this->statement->get('statement_type') === 'table') {
            $this->executeReal($statements);

            return $this;
        }*/

        $result = $this->executeReal($statements)->getResult();

        $this->resultReport->add($this->statement->get('resolved_statement_name'), $result, true);

        return $this;
    }
    /**
     * @param ArgumentBag|null $statement
     * @return mixed
     * @throws BlueDotRuntimeException
     */
    public function getResult(ArgumentBag $statement = null)
    {
        return $this->resultReport->get($this->statement->get('resolved_statement_name'));
    }

    protected function bindSingleParameter(Parameter $parameter, \PDOStatement $pdoStatement)
    {
        $pdoStatement->bindValue(
            $parameter->getKey(),
            $parameter->getValue(),
            $parameter->getType()
        );
    }

    private function executeReal(ArgumentBag $statements) : StrategyInterface
    {
        try {
/*            if (!$this->connection->getPDO()->inTransaction()) {
                $this->connection->getPDO()->beginTransaction();
            }*/

            if ($this->statement->has('foreign_key') and $this->statement->get('statement_type') === 'insert') {
                $currentStatementType = $this->statement->get('statement_type');

                if ($currentStatementType !== 'insert' and $currentStatementType !== 'update') {
                    throw new BlueDotRuntimeException(
                        sprintf('Invalid statement type. \'foreign_key\' options can only be used with \'insert\' or \'update\' statement for statement \'%s\'. Try using \'use\' option instead',
                            $this->statement->get('resolved_statement_name')
                        )
                    );
                }

                $foreignKey = $this->statement->get('foreign_key');
                $foreignKeyStatement = $statements->get($this->statement->get('scenario_name').'.'.$foreignKey->getName());

                if (!$this->resultReport->has($foreignKeyStatement->get('resolved_statement_name'))) {
                    $recursiveStatementExecution = new RecursiveStatementExecution(
                        $foreignKeyStatement,
                        $this->resultReport,
                        $this->connection
                    );

                    $recursiveStatementExecution->execute($statements);

                    unset($recursiveStatementExecution);
                }

                $result = $this->resultReport->get($foreignKeyStatement->get('resolved_statement_name'));

                if ($result instanceof MultipleInsertQueryResult and !$result->containsOnlyOne()) {
                    $insertedIds = $result->getInsertedIds();
                    $newParameters = array();

                    if ($this->statement->has('parameters')) {
                        $parameters = $this->statement->get('parameters');
                    }

                    foreach ($insertedIds as $id) {
                        $newParameters[$foreignKey->getBindTo()][] = $id;
                    }

                    $this->statement->add('parameters', $newParameters, true);
                    $this->statement->add('query_strategy', 'individual_multi_strategy', true);
                }
            }

            if (!$this->statement->has('query_strategy')) {
                throw new BlueDotRuntimeException(
                    sprintf(
                        'Invalid query strategy. Query strategy for %s could not been determined. This is a bug. Please, contact whitepostmail@gmail.com or post an issue on Github',
                        $this->statement->get('resolved_statement_name')
                    )
                );
            }
            
            $insertType = $this->statement->get('query_strategy');

            switch ($insertType) {
                case 'individual_strategy':
                    $this->individualStatement($statements);
                    break;
                case 'individual_multi_strategy':
                    $this->individualMultiStatement($statements);
                    break;
                case 'multi_strategy':
                    $this->multiStrategyStatement($statements);
                    break;
                default: throw new BlueDotRuntimeException('Internal Error. Query strategy not determined');
            }

/*            if ($this->connection->getPDO()->inTransaction()) {
                $this->connection->getPDO()->commit();
            }*/

            return $this;
        } catch (\PDOException $e) {
            $message = sprintf('A PDOException was thrown for statement %s with message \'%s\'', $this->statement->get('resolved_statement_name'), $e->getMessage());

/*            if ($this->connection->getPDO()->inTransaction()) {
                $this->connection->getPDO()->rollBack();
            }*/

            throw new BlueDotRuntimeException($message);
        }
    }

    private function individualStatement(ArgumentBag $statements)
    {
        $pdoStatement = $this->connection->getPDO()->prepare($this->statement->get('sql'));

        $this->handleUseOption($statements, $pdoStatement);
        $this->handleForeignKey($statements, $pdoStatement);

        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            foreach ($parameters as $key => $parameter) {
                $this->bindSingleParameter(new Parameter($key, $parameter), $pdoStatement);
            }
        }

        $pdoStatement->execute();

        $this->saveResult($pdoStatement);
    }

    private function individualMultiStatement(ArgumentBag $statements)
    {
        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            $bindParameter = array_keys($parameters)[0];
            $values = $parameters[$bindParameter];

            foreach ($values as $value) {
                $pdoStatement = $this->connection->getPDO()->prepare($this->statement->get('sql'));

                $this->handleForeignKey($statements, $pdoStatement);
                $this->handleUseOption($statements, $pdoStatement);

                $this->bindSingleParameter(new Parameter($bindParameter, $value), $pdoStatement);

                $pdoStatement->execute();

                $this->saveResult($pdoStatement);
            }
        }
    }

    private function multiStrategyStatement(ArgumentBag $statements)
    {
        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            foreach ($parameters as $realParameters) {
                $pdoStatement = $this->connection->getPDO()->prepare($this->statement->get('sql'));

                foreach ($realParameters as $key => $value) {
                    $this->handleForeignKey($statements, $pdoStatement);
                    $this->bindSingleParameter(new Parameter($key, $value), $pdoStatement);
                }

                $pdoStatement->execute();

                $this->saveResult($pdoStatement);
            }
        }
    }

    private function saveResult(\PDOStatement $pdoStatement)
    {
        $statementType = $this->statement->get('statement_type');

        $resolvedStatementName = $this->statement->get('resolved_statement_name');

        $queryResult = ResultReportContext::context(array(
            'statement_type' => $statementType,
            'pdo_statement' => $pdoStatement,
            'connection' => $this->connection,
        ))->makeReport();

        if ($queryResult instanceof InsertQueryResult) {
            if ($this->resultReport->has($resolvedStatementName)) {
                $this->resultReport->get($resolvedStatementName)->addInsertResult($queryResult);
            } else {
                $multipleInsertResult = new MultipleInsertQueryResult();

                $multipleInsertResult->addInsertResult($queryResult);

                $this->resultReport->add($resolvedStatementName, $multipleInsertResult);
            }

            return;
        }

        $this->resultReport->add($resolvedStatementName, $queryResult);
    }

    private function handleUseOption(ArgumentBag $statements, \PDOStatement $pdoStatement)
    {
        if ($this->statement->has('use_option')) {
            $useOption = $this->statement->get('use_option');
            $useStatement = $statements->get($this->statement->get('scenario_name').'.'.$useOption->getName());

            if (!$this->resultReport->has($useStatement->get('resolved_statement_name'))) {
                $recursiveStatementExecution = new RecursiveStatementExecution(
                    $useStatement,
                    $this->resultReport,
                    $this->connection
                );

                $result = $recursiveStatementExecution->execute($statements)->getResult();

                unset($recursiveStatementExecution);

                $this->resultReport->add($useStatement->get('resolved_statement_name'), $result, true);
            }

            $useOptionResult = $this->resultReport->get($useStatement->get('resolved_statement_name'));

            if (!$useOptionResult instanceof SelectQueryResult) {
                throw new BlueDotRuntimeException(
                    sprintf(
                        'Invalid use option result in statement \'%s\' that has use option statement \'%s\'. A use option query can only be a select query, cannot be empty and can only return a single row result. In cases where you don\'t know if the result will exist, add an \'if_exists\' option',
                        $this->statement->get('resolved_statement_name'),
                        $useStatement->get('resolved_statement_name')
                    )
                );
            }

            if (!$useOptionResult->getMetadata()->isOneRow()) {
                throw new BlueDotRuntimeException(sprintf(
                    'Invalid use option result. Results of \'use\' statements can only return one row and cannot be empty for statement \'%s\'',
                    $useStatement->get('resolved_statement_name')
                ));
            }

            foreach ($useOption->getValues() as $key => $parameterKey) {
                $exploded = explode('.', $key);

                $parameterValue = $useOptionResult->getQueryResult()[0][$exploded[1]];

                $this->bindSingleParameter(new Parameter($parameterKey, $parameterValue), $pdoStatement);
            }
        }
    }

    private function handleForeignKey(ArgumentBag $statements, \PDOStatement $pdoStatement)
    {
        if ($this->statement->has('foreign_key')) {
            $foreignKeyOption = $this->statement->get('foreign_key');
            $foreignKeyStatement = $statements->get($this->statement->get('scenario_name').'.'.$foreignKeyOption->getName());

            if (!$this->resultReport->has($foreignKeyStatement->get('resolved_statement_name'))) {
                $recursiveStatementExecution = new RecursiveStatementExecution(
                    $foreignKeyStatement,
                    $this->resultReport,
                    $this->connection
                );

                $result = $recursiveStatementExecution->execute($statements)->getResult();

                unset($recursiveStatementExecution);

                $this->resultReport->add($foreignKeyStatement->get('resolved_statement_name'), $result, true);
            }

            $foreignKeyResult = $this->resultReport->get($foreignKeyStatement->get('resolved_statement_name'));

            if (!$foreignKeyResult instanceof InsertQueryResult and !$foreignKeyResult instanceof MultipleInsertQueryResult) {
                throw new BlueDotRuntimeException(sprintf(
                    'Results of \'foreign_key\' statements can only return one row and cannot be empty for statement \'%s\'',
                    $foreignKeyStatement->get('resolved_statement_name')
                ));
            }

            $this->bindSingleParameter(new Parameter($foreignKeyOption->getBindTo(), $foreignKeyResult->getLastInsertId()), $pdoStatement);
        }
    }
}