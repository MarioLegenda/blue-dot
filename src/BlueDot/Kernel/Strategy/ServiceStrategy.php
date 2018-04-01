<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\BlueDotInterface;
use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Kernel\Connection\Connection;

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
     * @var StorageInterface $result
     */
    private $result;
    /**
     * @var array $parameters
     */
    private $parameters;
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * @var BlueDotInterface $blueDot
     */
    private $blueDot;
    /**
     * SimpleStrategy constructor.
     * @param ServiceConfiguration $configuration
     * @param Connection $connection
     */
    public function __construct(
        ServiceConfiguration $configuration,
        Connection $connection
    ) {
        $this->configuration = $configuration;
        $this->connection = $connection;
    }

    public function execute() : StrategyInterface
    {
        $dataType = $this->statement->get('data_type');
        if ($dataType === 'object') {
            $objectName = $this->statement->get('name');

            $object = new $objectName($this->blueDot, $this->parameters);

            if (!$object instanceof ServiceInterface) {
                throw new BlueDotRuntimeException(
                    sprintf(
                        'Service %s has to implement %s or extend %s',
                        $this->statement->get('name'),
                        ServiceInterface::class,
                        AbstractCallable::class
                    )
                );
            }

            $this->result = $object->run();
        }

        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }
}