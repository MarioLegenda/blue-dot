<?php

namespace BlueDot\Database\Execution;

use BlueDot\Database\Connection;
use BlueDot\Entity\Promise;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Exception\ConnectionException;

class PreparedExecution
{
    /**
     * @var array $statementNames
     */
    private $statementNames = array();
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * @var array $promises
     */
    private $promises = array();
    /**
     * @var StrategyInterface[] $strategies
     */
    private $strategies = array();
    /**
     * PreparedExecution constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    /**
     * @param string $name
     * @param StrategyInterface $strategy
     * @return PreparedExecution
     */
    public function addStrategy(string $name, StrategyInterface $strategy) : PreparedExecution
    {
        $this->statementNames[] = $name;
        $this->strategies[] = $strategy;

        return $this;
    }
    /**
     * @return PreparedExecution
     * @throws ConnectionException
     */
    public function execute() : PreparedExecution
    {
        if ($this->connection->getPDO()->inTransaction()) {
            throw new ConnectionException(
                sprintf(
                    'Internal prepared execution connection exception. There should not be any transaction but there is. This is a bug. Please, contact whitepostmail@gmail.com or post an issue on Github'
                )
            );
        }

        try {
            $this->connection->getPDO()->beginTransaction();

            foreach ($this->strategies as $key => $strategy) {
                $result = $strategy->execute()->getResult();

                $promise = $this->createPromise($result);

                $promise->setName($this->statementNames[$key]);

                $this->promises[$promise->getName()][] = $promise;
            }

            $this->connection->getPDO()->commit();
        } catch (\Exception $e) {
            if ($this->connection->getPDO()->inTransaction()) {
                $this->connection->getPDO()->rollBack();
            }

            throw $e;
        }


        return $this;
    }
    /**
     * @return PreparedExecution
     */
    public function clear() : PreparedExecution
    {
        $this->strategies = array();
        $this->promises = array();

        return $this;
    }
    /**
     * @return array
     */
    public function getPromises() : array
    {
        return $this->promises;
    }

    private function createPromise($result) : PromiseInterface
    {
        return new Promise($result);
    }
}