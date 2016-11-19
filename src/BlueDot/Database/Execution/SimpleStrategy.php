<?php

namespace BlueDot\Database\Execution;

use BlueDot\Database\Parameter\Parameter;
use BlueDot\Database\Parameter\ParameterCollection;

class SimpleStrategy extends AbstractStrategy implements StrategyInterface
{
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

    public function getResult()
    {

    }

    private function singleStatementExecution(ParameterCollection $parameters = null)
    {
        $this->pdoStatement = $this->connection->getConnection()->prepare($this->statement->get('sql'));

        $parameters = ($parameters instanceof ParameterCollection) ? $parameters : $this->statement->get('parameters');

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