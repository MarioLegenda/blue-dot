<?php

namespace BlueDot\Common;

use BlueDot\Database\ParameterConversion;
use BlueDot\Exception\CommonInternalException;
use BlueDot\Exception\QueryException;

class StatementValidator
{
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * @var ArgumentValidator $argumentValidator
     */
    private $argumentValidator;
    /**
     * @var array $configuration
     */
    private $configuration;
    /**
     * @var ParameterConversion $parameterConversion
     */
    private $parameterConversion;
    /**
     * @param ArgumentValidator $validator
     * @param array $configuration
     * @param ParameterConversion $parameterConversion
     */
    public function __construct(ArgumentValidator $validator, array $configuration, ParameterConversion $parameterConversion)
    {
        $this->argumentValidator = $validator;
        $this->configuration = $configuration;
        $this->parameterConversion = $parameterConversion;
    }
    /**
     * @return StatementValidator
     * @throws CommonInternalException
     * @throws QueryException
     */
    public function validate() : StatementValidator
    {
        $this->argumentValidator->validate();

        $type = $this->argumentValidator->getType();

        if (!array_key_exists($type, $this->configuration)) {
            throw new CommonInternalException('Invalid input. \''.$this->argumentValidator->getResolvedName().'\' does not exist');
        }

        $statementType = $this->configuration[$type];

        if (!$statementType->has($this->argumentValidator->getResolvedName())) {
            throw new CommonInternalException('Invalid input. \''.$this->argumentValidator->getResolvedName().'\' does not exist');
        }

        $statement = $this->configuration[$type]->get($this->argumentValidator->getResolvedName());

        $this->parameterConversion->convert($statement->get('type'), $statement);

        $this->statement = $statement;

        return $this;
    }
    /**
     * @return ArgumentBag
     */
    public function getStatement() : ArgumentBag
    {
        return $this->statement;
    }
}