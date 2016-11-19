<?php

namespace BlueDot\Database\Execution;

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

        $sql = $this->statement->get('sql');

        $this->pdoStatement = $this->connection->getConnection()->prepare($sql);

        if ($this->statement->has('parameters')) {
            $this->bindParameters($this->statement->get('parameters'));
        }

        $this->pdoStatement->execute();

        return $this;
    }

    public function getResult()
    {

    }
    /**
     * @param ParameterCollection $parameters
     */
    private function bindParameters(ParameterCollection $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->pdoStatement->bindValue(
                $parameter->getKey(),
                $parameter->getValue(),
                $parameter->getType()
            );
        }
    }
}