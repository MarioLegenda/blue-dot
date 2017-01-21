<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Database\Parameter\Parameter;
use BlueDot\Database\Parameter\ParameterCollection;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityCollection;
use BlueDot\Exception\BlueDotRuntimeException;

class SimpleStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * @var StorageInterface $entity
     */
    private $entity;
    /**
     * @return StrategyInterface
     * @throws BlueDotRuntimeException
     */
    public function execute() : StrategyInterface
    {
        try {
            $this->connection->connect();

            $insertType = $this->statement->get('query_strategy');

            $this->connection->getConnection()->beginTransaction();

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

            $this->connection->getConnection()->commit();

            return $this;
        } catch (\PDOException $e) {
            $message = sprintf('A PDOException was thrown for statement %s with message \'%s\'', $this->statement->get('resolved_statement_name'), $e->getMessage());

            $this->connection->getConnection()->rollBack();

            throw new BlueDotRuntimeException($message);
        }
    }
    /**
     * @param ArgumentBag|null $statement
     * @return StorageInterface|Entity|EntityCollection
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function getResult(ArgumentBag $statement = null)
    {
        $result = $this->resultReport->get($this->statement->get('resolved_statement_name'));
        $statementType = $this->statement->get('statement_type');

        if (is_null($result)) {
            return null;
        }

        if ($statementType === 'insert') {
            $entity = new Entity();

            foreach ($result as $key => $res) {
                $entity->add($key, (int) $res);
            }

            return $entity;
        } else if ($statementType === 'select') {
            if (count($result) === 1) {
                return new Entity($result[0][0]);
            }

            $temp = array();

            foreach ($result as $rows) {
                foreach ($rows as $key => $row) {
                    $temp[] = $row;
                }
            }

            return new Entity($temp);
        } else if ($statementType === 'update' or $statementType === 'delete') {
            return $result[0];
        }
    }

    private function individualStatement()
    {
        $pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            foreach ($parameters as $key => $parameter) {
                $this->bindSingleParameter(new Parameter($key, $parameter), $pdoStatement);
            }
        }

        $pdoStatement->execute();

        $this->saveResult($pdoStatement);
    }

    private function individualMultiStatement()
    {
        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            $bindParameter = array_keys($parameters)[0];
            $values = $parameters[$bindParameter];

            foreach ($values as $value) {
                $pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

                $this->bindSingleParameter(new Parameter($bindParameter, $value), $pdoStatement);

                $pdoStatement->execute();

                $this->saveResult($pdoStatement);
            }
        }
    }

    private function multiStrategyStatement()
    {
        if ($this->statement->has('parameters')) {
            $parameters = $this->statement->get('parameters');

            foreach ($parameters as $realParameters) {
                $pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

                foreach ($realParameters as $key => $value) {
                    $this->bindSingleParameter(new Parameter($key, $value), $pdoStatement);
                }

                $pdoStatement->execute();

                $this->saveResult($pdoStatement);
            }
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