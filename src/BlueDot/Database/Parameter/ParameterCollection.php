<?php

namespace BlueDot\Database\Parameter;

use BlueDot\Exception\CommonInternalException;
use BlueDot\Exception\ConfigurationException;

class ParameterCollection
{
    /**
     * @var array $parameters
     */
    private $parameters;
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        if (!empty($parameters)) {
            foreach ($parameters as $key => $value) {
                $this->parameters[] = new Parameter($key, $value);
            }
        }
    }
    /**
     * @param Parameter $parameter
     * @return $this
     */
    public function addParameter(Parameter $parameter) : ParameterCollection
    {
        $this->parameters[] = $parameter;

        return $this;
    }
}