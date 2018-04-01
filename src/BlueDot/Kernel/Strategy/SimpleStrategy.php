<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Common\ArgumentBag;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Parameter\Parameter;
use BlueDot\Entity\Entity;
use BlueDot\Entity\ModelConverter;
use BlueDot\Exception\BlueDotRuntimeException;

class SimpleStrategy implements StrategyInterface
{
    /**
     * @var SimpleConfiguration $configuration
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
     * @var \PDOStatement $pdoStatement
     */
    protected $pdoStatement;
    /**
     * SimpleStrategy constructor.
     * @param SimpleConfiguration $configuration
     * @param Connection $connection
     */
    public function __construct(
        SimpleConfiguration $configuration,
        Connection $connection
    ) {
        $this->configuration = $configuration;
        $this->connection = $connection;
    }
    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $this->connection->connect();

            $this->connection->getPDO()->beginTransaction();

            $this->doExecute();

            $this->connection->getPDO()->commit();
        } catch (\PDOException $e) {
            $message = sprintf('A PDOException was thrown for statement %s with message \'%s\'', $this->statement->get('resolved_statement_name'), $e->getMessage());

            throw new \RuntimeException($message);
        }
    }
    /**
     * @throws \RuntimeException
     */
    private function doExecute()
    {
        $sql = $this->configuration->getWorkConfig()->getSql();
        $userParameters = $this->configuration->getWorkConfig()->getUserParameters();

        $pdoStatement = $this->connection->getPDO()->prepare($sql);

        if (!empty($userParameters)) {
            foreach ($userParameters as $key => $parameter) {
                $this->bindSingleParameter(new Parameter($key, $parameter), $pdoStatement);
            }
        }

        $pdoStatement->execute();

        $this->saveResult($pdoStatement);
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
            $result->setName('simple');
            return $result;
        }

        $statementType = $this->statement->get('statement_type');

        if (is_null($result)) {
            return new Entity(null, 'simple');
        }

        if ($statementType === 'insert') {
            $entity = $this->createInsertResult();

            $entity->setName('simple');

            return $entity;
        } else if ($statementType === 'select') {
            $entity = $this->createSelectResult();

            if ($entity instanceof Entity) {
                $entity->setName('simple');
            }

            return $entity;
        } else if ($statementType === 'update' or $statementType === 'delete') {
            $rowsAffected = $result[0];

            $entity = new Entity();

            $entity->setName('simple');

            $entity->add('rows_affected', $rowsAffected);

            return $entity;
        } else {
            $entity = new Entity();

            $entity->setName('simple');

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

            if (is_numeric($result[0])) {
                $result[0] = (int) $result[0];

                $entity->add('inserted_ids', $result);
                $entity->add('last_insert_id', (int) $result[0]);
            } else {
                $entity->add('inserted_ids', $result);
                $entity->add('last_insert_id', $result[0]);
            }

            return $entity;
        }

        if ($resultCount > 1) {
            foreach ($result as $key => $value) {
                $result[$key] = (int) $value;
            }

            $entity->add('inserted_ids', $result);
            $entity->add('last_insert_id', (int) $result[$resultCount - 1]);

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
}
