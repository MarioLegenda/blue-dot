<?php

namespace BlueDot\Common;

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
     * @param ArgumentValidator $validator
     * @param array $configuration
     */
    public function __construct(ArgumentValidator $validator, array $configuration)
    {
        $this->argumentValidator = $validator;
        $this->configuration = $configuration;
    }
    /**
     * @return $this
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

        $this->statement = $this->configuration[$type]->get($this->argumentValidator->getResolvedName());

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