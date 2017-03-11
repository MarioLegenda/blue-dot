<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Database\Parameter\Parameter;
use BlueDot\Database\Parameter\ParameterCollection;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityCollection;
use BlueDot\Entity\ModelConverter;
use BlueDot\Exception\BlueDotRuntimeException;

class SimpleStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * @var \PDOStatement $pdoStatement
     */
    protected $pdoStatement;
    /**
     * @return StrategyInterface
     * @throws BlueDotRuntimeException
     */
    public function execute() : StrategyInterface
    {
        try {
            $this->connection->connect();

            $insertType = $this->statement->get('query_strategy');

            if (!$this->connection->getConnection()->inTransaction()) {
                $this->connection->getConnection()->beginTransaction();
            }

            switch ($insertType) {
                case 'individual_strategy':
                    $this->individualStatement();
                    break;
                case 'individual_multi_strategy':
                    $this->individualMultiStatement();
                    break;
                case 'multi_strategy':
                    $this->multiStrategyStatement();
            }

            if ($this->connection->getConnection()->inTransaction()) {
                $this->connection->getConnection()->commit();
            }

            return $this;
        } catch (\PDOException $e) {
            $message = sprintf('A PDOException was thrown for statement %s with message \'%s\'', $this->statement->get('resolved_statement_name'), $e->getMessage());

            if ($this->connection->getConnection()->inTransaction()) {
                $this->connection->getConnection()->rollBack();
            }

            throw new BlueDotRuntimeException($message);
        }
    }
    /**
     * @param ArgumentBag|null $statement
     * @returns Entity
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function getResult(ArgumentBag $statement = null)
    {
        $result = $this->resultReport->get($this->statement->get('resolved_statement_name'));

        if ($result instanceof Entity) {
            return $result;
        }

        $statementType = $this->statement->get('statement_type');

        if (is_null($result)) {
            return new Entity();
        }

        if ($statementType === 'insert') {
            $entity = new Entity();
            $resultCount = count($result);

            if ($resultCount === 1) {
                $entity->add('inserted_ids', $result);
                $entity->add('last_insert_id', $result[0]);

                return $entity;
            }

            if ($resultCount > 1) {
                $entity->add('inserted_ids', $result);
                $entity->add('last_insert_id', $result[$resultCount - 1]);

                return $entity;
            }

            return $entity;
        } else if ($statementType === 'select') {
            if ($this->statement->has('model')) {
                $modelConverter = new ModelConverter($this->statement->get('model'), $result->toArray()[0]);

                $converted = $modelConverter->convertIntoModel();

                if (is_array($converted)) {
                    return new Entity($converted);
                }

                return $converted;
            }

            $temp = array();

            if (count($result[0]) > 1) {
                foreach ($result as $rows) {
                    foreach ($rows as $key => $row) {
                        $temp[] = $row;
                    }
                }

                return new Entity($temp);
            }

            if (count($result[0]) === 1) {
                return new Entity($result[0]);
            }
        } else if ($statementType === 'update' or $statementType === 'delete') {
            $rowsAffected = $result[0];

            $entity = new Entity();

            $entity->add('rows_affected', $rowsAffected);

            return $entity;
        }
    }

    private function bindSingleParameter(Parameter $parameter, \PDOStatement $pdoStatement)
    {
        $pdoStatement->bindValue(
            $parameter->getKey(),
            $parameter->getValue(),
            $parameter->getType()
        );
    }

    private function individualStatement()
    {
        $this->pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            foreach ($parameters as $key => $parameter) {
                $this->bindSingleParameter(new Parameter($key, $parameter), $this->pdoStatement);
            }
        }

        $this->pdoStatement->execute();

        $this->saveResult($this->pdoStatement);
    }

    private function individualMultiStatement()
    {
        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            $bindParameter = array_keys($parameters)[0];
            $values = $parameters[$bindParameter];

            foreach ($values as $value) {
                $this->pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

                $this->bindSingleParameter(new Parameter($bindParameter, $value), $this->pdoStatement);

                $this->pdoStatement->execute();

                $this->saveResult($this->pdoStatement);
            }
        }
    }

    private function multiStrategyStatement()
    {
        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            foreach ($parameters as $realParameters) {
                $this->pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

                foreach ($realParameters as $key => $value) {
                    $this->bindSingleParameter(new Parameter($key, $value), $this->pdoStatement);
                }

                $this->pdoStatement->execute();

                $this->saveResult($this->pdoStatement);
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
}