<?php

namespace BlueDot\Database\Execution\LowLevelStrategy;

use BlueDot\Database\Execution\StrategyInterface;
use BlueDot\Database\Connection;
use BlueDot\Common\ArgumentBag;

use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Common\StorageInterface;
use BlueDot\Entity\Entity;
use BlueDot\Database\Parameter\Parameter;

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

        $this->connection->connect();
    }
    /**
     * @param ArgumentBag $statements
     * @return StrategyInterface
     * @throws BlueDotRuntimeException
     */
    public function execute(ArgumentBag $statements = null) : StrategyInterface
    {
        if ($this->statement->get('statement_type') === 'database' or $this->statement->get('statement_type') === 'table') {
            $this->executeReal($statements);

            return $this;
        }


        $result = $this->executeReal($statements)->getResult();

        $this->resultReport->add($this->statement->get('resolved_statement_name'), $result, true);

        return $this;
    }
    /**
     * @param ArgumentBag|null $statement
     * @return StorageInterface|Entity|EntityCollection
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function getResult(ArgumentBag $statement = null)
    {
        $result = $this->resultReport->get($this->statement->get('resolved_statement_name'));

        if (is_null($result)) {
            throw new BlueDotRuntimeException(sprintf(
                'Empty result returned for statement \'%s\'. Scenario statements have to have a result and cannot be empty',
                $this->statement->get('resolved_statement_name')
            ));
        }
        if ($result instanceof Entity) {
            return $result;
        }

        $statementType = $this->statement->get('statement_type');

        if ($statementType === 'insert') {
            $entity = new Entity();

            $rowsReturned = 0;
            foreach ($result as $key => $res) {
                $entity->add($key, (int) $res);
                $rowsReturned++;
            }

            $entity->add('rows_returned', $rowsReturned);

            return $entity;
        } else if ($statementType === 'select') {
            if (count($result) === 1) {
                $entity = new Entity($result[0][0]);

                $entity->add('rows_returned', 1);

                return $entity;
            }

            $temp = array();

            $rowsReturned = 0;
            foreach ($result as $rows) {
                foreach ($rows as $key => $row) {
                    $rowsReturned++;
                    $temp[] = $row;
                }
            }

            $entity = new Entity($temp);
            $entity->add('rows_returned', $rowsReturned);

            return $entity;
        } else if ($statementType === 'update' or $statementType === 'delete') {
            return $result[0];
        }
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
            $insertType = $this->statement->get('query_strategy');

            if (!$this->connection->getConnection()->inTransaction()) {
                $this->connection->getConnection()->beginTransaction();
            }

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

            if (!$this->connection->getConnection()->inTransaction()) {
                $this->connection->getConnection()->commit();
            }

            return $this;
        } catch (\PDOException $e) {
            $message = sprintf('A PDOException was thrown for statement %s with message \'%s\'', $this->statement->get('resolved_statement_name'), $e->getMessage());

            if (!$this->connection->getConnection()->inTransaction()) {
                $this->connection->getConnection()->rollBack();
            }

            throw new BlueDotRuntimeException($message);
        }
    }

    private function individualStatement(ArgumentBag $statements)
    {
        $pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            $this->handleUseOption($statements, $pdoStatement);
            $this->handleForeignKey($statements, $pdoStatement);

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
                $pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

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
                $pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

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
        if ($statementType === 'select') {

            $resolvedStatementName = $this->statement->get('resolved_statement_name');
            $queryResult = $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($queryResult)) {
                $this->resultReport->add($resolvedStatementName, null);
            } else {
                $this->resultReport->appendValue($resolvedStatementName, $queryResult);
            }
        } else if ($statementType === 'insert') {
            $resolvedStatementName = $this->statement->get('resolved_statement_name');
            $lastInsertId = $this->connection->getConnection()->lastInsertId();

            if (empty($lastInsertId)) {
                $this->resultReport->add($resolvedStatementName, null);
            } else {
                $this->resultReport->appendValue(
                    $resolvedStatementName,
                    $this->connection->getConnection()->lastInsertId()
                );
            }
        } else if ($statementType === 'update' or $statementType === 'delete') {
            $resolvedStatementName = $this->statement->get('resolved_statement_name');
            $rowCount = $pdoStatement->rowCount();

            if (empty($rowCount)) {
                $this->resultReport->add($resolvedStatementName, null);
            } else {
                $this->resultReport->appendValue(
                    $resolvedStatementName,
                    $pdoStatement->rowCount()
                );
            }
        }
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

                $this->resultReport->add($useStatement->get('resolved_statement_name'), $result, true);
            }

            $useOptionResult = $this->resultReport->get($useStatement->get('resolved_statement_name'));

            if (is_null($useOptionResult)) {
                throw new BlueDotRuntimeException(sprintf(
                    'Results of \'use\' statements can only return one row and cannot be empty for statement \'%s\'',
                    $useStatement->get('resolved_statement_name')
                ));
            }

            foreach ($useOption->getValues() as $key => $parameterKey) {
                $exploded = explode('.', $key);

                $parameterValue = $useOptionResult->get($exploded[1]);

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

                $this->resultReport->add($foreignKeyStatement->get('resolved_statement_name'), $result, true);
            }

            $foreignKeyResult = $this->resultReport->get($foreignKeyStatement->get('resolved_statement_name'));

            if (is_null($foreignKeyResult)) {
                throw new BlueDotRuntimeException(sprintf(
                    'Results of \'foreign_key\' statements can only return one row and cannot be empty for statement \'%s\'',
                    $foreignKeyStatement('resolved_statement_name')
                ));
            }

            $foreignKey = $foreignKeyResult[0];

            $this->bindSingleParameter(new Parameter($foreignKeyOption->getBindTo(), $foreignKey), $pdoStatement);
        }
    }
}