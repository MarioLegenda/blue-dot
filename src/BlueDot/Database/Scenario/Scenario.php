<?php

namespace BlueDot\Database\Scenario;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Database\ParameterCollectionInterface;

class Scenario
{
    /**
     * @var string $sqlType
     */
    private $sqlType;

    private $parameters;
    /**
     * @var string $sql
     */
    private $sql;
    /**
     * @var ArgumentBag $argumentBag
     */
    private $argumentBag;
    /**
     * @param StorageInterface $argumentBag
     */
    public function __construct(StorageInterface $argumentBag)
    {
        $this->argumentBag = $argumentBag;

        $this->sql = $argumentBag->get('specific_configuration')->getStatement();
        $this->parameters = $argumentBag->get('parameters');
    }
    /**
     * @return ArgumentBag|StorageInterface
     */
    public function getArgumentBag() : StorageInterface
    {
        return $this->argumentBag;
    }
    /**
     * @return string
     */
    public function getSql() : string
    {
        return $this->sql;
    }
    /**
     * @return bool
     */
    public function hasParameters() : bool
    {
        return $this->parameters instanceof ParameterCollectionInterface;
    }
    /**
     * @return mixed
     */
    public function getParameters() : ParameterCollectionInterface
    {
        return $this->parameters;
    }
}