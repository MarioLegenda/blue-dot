<?php

namespace BlueDot\Database;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\ArgumentValidator;
use BlueDot\Exception\CommonInternalException;
use BlueDot\Exception\QueryException;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

class ParameterConversion
{
    /**
     * @var ParameterConversion $instance
     */
    private static $instance;
    /**
     * @var array $userParameters
     */
    private $userParameters;
    /**
     * @var ArgumentBag $statement;
     */
    private $statement;
    /**
     * @param array $userParameters
     * @param ArgumentBag $statement
     * @return ParameterConversion
     */
    public static function instance(array $userParameters, ArgumentBag $statement) : ParameterConversion
    {
        return (self::$instance instanceof self) ? self::$instance : new self($userParameters, $statement);
    }
    /**
     * ParameterConversion constructor.
     * @param array $userParameters
     * @param ArgumentBag $statement
     */
    private function __construct(array $userParameters, ArgumentBag $statement)
    {
        $this->userParameters = $userParameters;
        $this->statement = $statement;
    }
    /**
     * @throws QueryException
     */
    public function convert()
    {
        $type = $this->statement->get('type');

        if ($type === 'simple') {
            if ($this->statement->has('parameters')) {
                $this->convertSimpleParameters($this->statement, $this->userParameters);
            }
        } else if ($type === 'scenario') {
            $statements = $this->statement->get('statements');

            foreach ($statements as $singleStatement) {
                if ($singleStatement->has('parameters')) {
                    $statementName = $singleStatement->get('statement_name');

                    if (!array_key_exists($statementName, $this->userParameters)) {
                        throw new QueryException('Configuration has parameters to bound but you haven\'t supplied any for '.$singleStatement->get('resolved_statement_name'));
                    }

                    $this->convertSimpleParameters($singleStatement, $this->userParameters[$statementName]);
                }
            }
        }
    }

    private function convertSimpleParameters(ArgumentBag $statement, $userParameters = array())
    {
        if (empty($userParameters)) {
            throw new QueryException('Statement '.$statement->get('resolved_name').' has parameters in the configuration but none are provided');
        }

        $configParameters = $statement->get('parameters');

        foreach ($userParameters as $parameters) {
            if (is_array($parameters)) {
                if (!$statement->has('multi_insert')) {
                    $statement->add('multi_insert', true);
                }
            }
        }

        $parameters = $configParameters
            ->compare($userParameters)
            ->bindValues($userParameters, $statement->has('multi_insert'));

        $statement->add('parameters', $parameters, true);
    }
}