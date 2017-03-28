<?php

namespace BlueDot\Database\Execution;

use BlueDot\Cache\CacheStorage;
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

            if (!$this->connection->getPDO()->inTransaction()) {
                $this->connection->getPDO()->beginTransaction();
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

            if ($this->connection->getPDO()->inTransaction()) {
                $this->connection->getPDO()->commit();
            }

            return $this;
        } catch (\PDOException $e) {
            $message = sprintf('A PDOException was thrown for statement %s with message \'%s\'', $this->statement->get('resolved_statement_name'), $e->getMessage());

            if ($this->connection->getPDO()->inTransaction()) {
                $this->connection->getPDO()->rollBack();
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
            return $this->createInsertResult();
        } else if ($statementType === 'select') {
            return $this->createSelectResult();
        } else if ($statementType === 'update' or $statementType === 'delete') {
            $rowsAffected = $result[0];

            $entity = new Entity();

            $entity->add('rows_affected', $rowsAffected);

            return $entity;
        } else {
            $entity = new Entity();

            $entity->add('rows_affected', 0);

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
        $this->pdoStatement = $this->connection->getPDO()->prepare($this->statement->get('sql'));

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
                $this->pdoStatement = $this->connection->getPDO()->prepare($this->statement->get('sql'));

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
                $this->pdoStatement = $this->connection->getPDO()->prepare($this->statement->get('sql'));

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
            $lastInsertId = $this->connection->getPDO()->lastInsertId();

            if (empty($lastInsertId)) {
                $this->resultReport->add($resolvedStatementName, null);
            } else {
                $this->resultReport->appendValue(
                    $resolvedStatementName,
                    $this->connection->getPDO()->lastInsertId()
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
        } else {
            $resolvedStatementName = $this->statement->get('resolved_statement_name');

            $this->resultReport->add($resolvedStatementName, 'Database or table query executed');
        }
    }

    private function createInsertResult() : Entity
    {
        $result = $this->resultReport->get($this->statement->get('resolved_statement_name'));

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
    }

    private function createSelectResult()
    {
        $result = $this->resultReport->get($this->statement->get('resolved_statement_name'));

        if ($this->statement->has('model')) {
            $this->saveInCache($result->toArray());

            $modelConverter = new ModelConverter($this->statement->get('model'), $result->toArray()[0]);

            $converted = $modelConverter->convertIntoModel();

            if (is_array($converted)) {
                $entity = new Entity($converted);

                $this->saveInCache($converted);

                return $entity;
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

            $this->saveInCache($temp);

            $entity = new Entity($temp);

            return $entity;
        }

        if (count($result[0]) === 1) {
            $this->saveInCache($result[0]);

            $entity = new Entity($result[0]);

            return $entity;
        }
    }

    private function saveInCache($result)
    {
        if ($this->statement->has('cache') and $this->statement->get('cache') === true) {
            if (CacheStorage::getInstance()->canBeCached($this->statement)) {
                $cache = CacheStorage::getInstance();
                $name = $cache->createName($this->statement);

                if (!$cache->has($name)) {
                    $cache->put($name, $result);
                }
            }
        }
    }
}
