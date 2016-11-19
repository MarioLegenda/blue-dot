<?php

namespace BlueDot\Database;

use BlueDot\Common\ArgumentBag;
use BlueDot\Database\Parameter\ParameterCollection;

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

    }
}