<?php

namespace BlueDot\Database;

use BlueDot\Common\ArgumentBag;
use BlueDot\Exception\QueryException;

class ParameterConversion
{
    /**
     * @var array $userParameters
     */
    private $userParameters;
    /**
     * @param array $userParameters
     */
    public function __construct(array $userParameters)
    {
        $this->userParameters = $userParameters;
    }
    /**
     * @param string $type
     * @param ArgumentBag $statement
     */
    public function convert(string $type, ArgumentBag $statement)
    {
        if ($type === 'simple') {
            if ($statement->has('parameters')) {
                $this->convertSimpleParameters($statement, $this->userParameters);
            }
        } else if ($type === 'scenario') {
            $statements = $statement->get('statements');

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

        $configParameters
            ->compare($userParameters)
            ->bindValues($userParameters);
    }
}