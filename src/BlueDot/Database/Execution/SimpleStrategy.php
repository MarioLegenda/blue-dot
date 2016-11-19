<?php

namespace BlueDot\Database\Execution;

use BlueDot\Common\StorageInterface;
use BlueDot\Database\Parameter\Parameter;
use BlueDot\Database\Parameter\ParameterCollection;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityCollection;

class SimpleStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * @var StorageInterface $entity
     */
    private $entity;
    /**
     * @var \PDOStatement $pdoStatement
     */
    private $pdoStatement;
    /**
     * @return StrategyInterface
     */
    public function execute() : StrategyInterface
    {
        $this->connection->connect();

        $multiValue = $this->statement->has('multi_insert');

        $this->connection->getConnection()->beginTransaction();

        switch ($multiValue) {
            case false:
                $this->singleStatementExecution();
                break;
            case true:
                $this->multiInsertStatementExecution();
                break;
        }

        $this->connection->getConnection()->commit();

        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getResult() : StorageInterface
    {
        if ($this->entity instanceof StorageInterface) {
            return $this->entity;
        }

        if ($this->statement->get('statement_type') === 'select') {
            $result = $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);

            $resultCount = count($result);

            switch ($resultCount) {
                case 0:
                    $this->entity = new Entity();

                    return $this->entity;
                case 1:
                    $this->entity = new Entity($result[0]);

                    return $this->entity;
                default:
                    $this->entity = new EntityCollection($result);

                    return $this->entity;
            }
        }
    }

    private function singleStatementExecution(ParameterCollection $parameters = null)
    {
        $this->pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

        if (!$parameters instanceof ParameterCollection) {
            if ($this->statement->has('parameters')) {
                $parameters = $this->statement->get('parameters');
            }
        }

        if ($this->statement->has('parameters')) {
            $this->bindParameterCollection($parameters);
        }

        $this->pdoStatement->execute();
    }

    private function bindParameterCollection(ParameterCollection $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->bindSingleParameter($parameter);
        }
    }

    private function bindSingleParameter(Parameter $parameter)
    {
        $this->pdoStatement->bindValue(
            $parameter->getKey(),
            $parameter->getValue(),
            $parameter->getType()
        );
    }

    private function multiInsertStatementExecution()
    {
        $parameters = $this->resolveMultiInsertParameters();

        foreach ($parameters as $parameter) {
            $this->singleStatementExecution($parameter);
        }
    }


    private function resolveMultiInsertParameters() : array
    {
        $parameters = $this->statement->get('parameters');

        $valueCount = count($parameters[0]->getValue());

        $keys  = $parameters->getKeys();
        $values = $parameters->getAllValues();

        $parameters = array();
        $i = 0;
        while ($i < $valueCount) {
            $parameterCollection = new ParameterCollection();

            foreach ($keys as $key => $val) {
                $parameter = new Parameter($val, $values[$key][$i]);
                $parameterCollection->addParameter($parameter);
            }

            $parameters[] = $parameterCollection;

            $i++;
        }

        return $parameters;
    }
}