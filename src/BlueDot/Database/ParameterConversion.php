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
                $this->convertSimpleParameters($statement);
            }
        }
    }

    private function convertSimpleParameters(ArgumentBag $statement)
    {
        if (empty($this->userParameters)) {
            throw new QueryException('Statement '.$statement->get('resolved_name').' has parameters in the configuration but none are provided');
        }

        $configParameters = $statement->get('parameters');

        $configParameters
            ->compare($this->userParameters)
            ->bindValues($this->userParameters);
    }
}