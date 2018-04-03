<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Common\Util\Util;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Entity\Promise;
use BlueDot\Exception\ConnectionException;
use BlueDot\Kernel\Kernel;

class PreparedExecution
{
    private $connection;
    /**
     * @var array $promises
     */
    private $promises = array();
    /**
     * @var Kernel[] $kernels
     */
    private $kernels = array();
    /**
     * PreparedExecution constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    /**
     * @param Kernel $kernel
     * @return PreparedExecution
     */
    public function addKernel(Kernel $kernel) : PreparedExecution
    {
        $this->kernels[] = $kernel;

        return $this;
    }
    /**
     * @return PreparedExecution
     * @throws ConnectionException
     * @throws \Exception
     */
    public function execute() : PreparedExecution
    {
        $this->connection->connect();

        if ($this->connection->getPDO()->inTransaction()) {
            throw new ConnectionException(
                sprintf(
                    'Internal prepared execution connection exception. There should not be any transaction but there is. This is a bug. Please, contact whitepostmail@gmail.com or post an issue on Github'
                )
            );
        }

        try {
            $this->connection->getPDO()->beginTransaction();

            $kernelsGenerator = Util::instance()->createGenerator($this->kernels);

            foreach ($kernelsGenerator as $item) {
                /** @var Kernel $kernel */
                $kernel = $item['item'];

                $kernel->validateKernel();

                $strategy = $kernel->createStrategy($this->connection);

                $kernelResult = $kernel->executeStrategy($strategy, true);

                $entity = $kernel->convertKernelResultToUserFriendlyResult($kernelResult);

                $promise = new Promise(
                    $entity,
                    $entity->getName()
                );

                if (array_key_exists($promise->getName(), $this->promises)) {
                    $this->promises[$promise->getName()][] = $promise;

                    continue;
                }

                $this->promises[] = $promise;
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
        $this->kernels = array();
        $this->promises = array();

        gc_collect_cycles();

        return $this;
    }
    /**
     * @return array
     */
    public function getPromises() : array
    {
        return $this->promises;
    }
}