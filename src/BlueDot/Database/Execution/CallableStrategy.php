<?php

namespace BlueDot\Database\Execution;

use BlueDot\BlueDotInterface;
use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Common\CallableInterface;

class CallableStrategy implements StrategyInterface
{
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
     * @param ArgumentBag $statement
     * @param BlueDotInterface $blueDot
     * @param array $parameters
     * @throws BlueDotRuntimeException
     */
    public function __construct(ArgumentBag $statement, BlueDotInterface $blueDot, array $parameters)
    {
        $this->statement = $statement;
        $this->blueDot = $blueDot;
        $this->parameters = $parameters;
    }

    public function execute() : StrategyInterface
    {
        $dataType = $this->statement->get('data_type');
        if ($dataType === 'object') {
            $objectName = $this->statement->get('name');

            $object = new $objectName($this->blueDot, $this->parameters);

            if (!$object instanceof CallableInterface) {
                throw new BlueDotRuntimeException('Callable '.$this->statement->get('name').' has to implement '.CallableInterface::class);
            }

            $this->result = $object->run();
        }
        return $this;
    }

    public function getResult() : StorageInterface
    {
        return $this->result;
    }
}