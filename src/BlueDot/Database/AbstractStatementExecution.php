<?php

namespace BlueDot\Database;

use BlueDot\Common\StorageInterface;

abstract class AbstractStatementExecution
{
    /**
     * @return mixed
     */
    abstract public function execute();

    /**
     * @var StorageInterface $argumentsBag
     */
    protected $argumentsBag;
    /**
     * @param StorageInterface $argumentsBag
     */
    public function __construct(StorageInterface $argumentsBag)
    {
        $this->argumentsBag = $argumentsBag;
    }

    protected function isValueResolvable($value) : bool
    {
        return is_bool($value) or is_string($value) or $value === null or is_int($value);
    }
}