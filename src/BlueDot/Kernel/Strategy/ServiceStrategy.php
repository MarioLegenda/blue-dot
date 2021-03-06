<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\BlueDotInterface;
use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Configuration\Flow\Service\ServiceInterface;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Kernel\Result\Service\KernelResult;

class ServiceStrategy implements StrategyInterface
{
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * @var ServiceConfiguration $configuration
     */
    private $configuration;
    /**
     * @var BlueDotInterface $blueDot
     */
    private $blueDot;
    /**
     * SimpleStrategy constructor.
     * @param ServiceConfiguration $configuration
     * @param Connection $connection
     * @param BlueDotInterface $blueDot
     */
    public function __construct(
        BlueDotInterface $blueDot,
        ServiceConfiguration $configuration,
        Connection $connection
    ) {
        $this->configuration = $configuration;
        $this->connection = $connection;
        $this->blueDot = $blueDot;
    }
    /**
     * @inheritdoc
     */
    public function execute(bool $delayedTransactionCommit) : KernelResultInterface
    {
        $class = $this->configuration->getClass();
        $userParameters = $this->configuration->getUserParameters();

        /** @var ServiceInterface $service */
        $service = new $class($this->blueDot, $userParameters);

        $result = $service->run();

        return new KernelResult(
            $this->configuration,
            $result
        );
    }
    /**
     * @inheritdoc
     * @throws \RuntimeException
     */
    public function getResult(\PDOStatement $pdoStatement = null) : KernelResultInterface
    {
        $class = get_class($this);

        $message = sprintf(
            '\'%s::getResult()\' is not implemented in \'%s\'',
            StrategyInterface::class,
            $class
        );

        throw new \RuntimeException($message);
    }
}