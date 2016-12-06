<?php

namespace BlueDot\Common;

use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Exception\CompileException;

class ArgumentValidator implements ValidatorInterface
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
    public function __construct(string $arguments = null)
    {
        $this->arguments = $arguments;
    }
    /**
     * @param string $arguments
     * @return ValidatorInterface
     */
    public function setValidationArgument($validationArgument) : ValidatorInterface
    {
        if (!is_string($validationArgument)) {
            throw new CompileException('Invalid argument for validation in '.ArgumentValidator::class.'. This is probably a bug so please, contact whitepostmail@gmail.com or post an issue');
        }

        $this->arguments = $validationArgument;

        return $this;
    }
    /**
     * @return bool
     * @throws BlueDotRuntimeException
     */
    public function validate() : ValidatorInterface
    {
        $argc = explode('.', $this->arguments);

        if (count($argc) === 2) {
            $this->type = $argc[0];
            $this->statementName = $argc[1];
            $this->resolvedName = $argc[0].'.'.$argc[1];

            return $this;
        }

        if (count($argc) === 3) {
            $this->type = $argc[0];
            $this->simpleType = $argc[1];
            $this->statementName = $argc[2];
            $this->resolvedName = $argc[0].'.'.$argc[1].'.'.$argc[2];

            return $this;
        }

        throw new BlueDotRuntimeException('Invalid execute statement name. Given '.$this->arguments);
    }
    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    /**
     * @return string
     */
    public function getResolvedName() : string
    {
        return $this->resolvedName;
    }
    /**
     * @return string
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