<?php

namespace BlueDot\Common;

use BlueDot\Exception\QueryException;

class ArgumentValidator
{
    /**
     * @var string $type
     */
    private $type;
    /**
     * @var string $resolvedName
     */
    private $resolvedName;
    /**
     * @var string $scenarioName
     */
    private $simpleType;
    /**
     * @var string $statementName
     */
    private $statementName;
    /**
     * @var string $arguments
     */
    private $arguments;
    /**
     * @param string $arguments
     */
    public function __construct(string $arguments)
    {
        $this->arguments = $arguments;
    }
    /**
     * @param string $arguments
     * @return bool
     * @throws QueryException
     */
    public function validate() : bool
    {
        $argc = explode('.', $this->arguments);

        if (count($argc) === 2) {
            $this->type = $argc[0];
            $this->statementName = $argc[1];
            $this->resolvedName = $argc[0].'.'.$argc[1];

            return true;
        }

        if (count($argc) === 3) {
            $this->type = $argc[0];
            $this->simpleType = $argc[1];
            $this->statementName = $argc[2];
            $this->resolvedName = $argc[0].'.'.$argc[1].'.'.$argc[2];

            return true;
        }

        throw new QueryException('Invalid execute statement name. Given '.$this->arguments);
    }
    /**
     * @return mixed
     */
    public function getType() : string
    {
        return $this->type;
    }
    /**
     * @return mixed
     */
    public function getResolvedName() : string
    {
        return $this->resolvedName;
    }
    /**
     * @return mixed
     */
    public function getStatementName() : string
    {
        return $this->statementName;
    }
    /**
     * @return mixed
     */
    public function getSimpleType()
    {
        return $this->simpleType;
    }
}