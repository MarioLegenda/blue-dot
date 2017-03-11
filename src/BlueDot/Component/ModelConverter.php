<?php

namespace BlueDot\Component;

use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Database\ParameterConversion;

class ModelConverter
{
    public function modelToParameters(array $configParameters, $userParameters)
    {
        $parameterType = null;
        $convertedParameters = array();

        $this->classExistsCheck($userParameters);

        foreach ($configParameters as $configParameter) {
            $method = 'get'.str_replace('_', '', ucwords($configParameter, '_'));

            if (!method_exists($userParameters, $method)) {
                throw new BlueDotRuntimeException(
                    sprintf('Invalid parameter. Method %s does not exist in object %s that is to be bound to config parameter %s',
                        $method,
                        get_class($userParameters),
                        $configParameter
                    )
                );
            }

            $convertedParameters[$configParameter] = $userParameters->{$method}();
        }

        return array(
            'converted_parameters' => $convertedParameters,
        );
    }

    public function multipleModelsToParameters(array $configParameters, $userParameters)
    {
        $convertedParameters = array();
        $oneObjectExists = false;
        foreach ($userParameters as $parameter) {
            if (is_object($parameter)) {
                $oneObjectExists = true;

                $convertedParameters[] = $this->modelToParameters($configParameters, $parameter)['converted_parameters'];
            } else {
                $convertedParameters[] = $parameter;
            }
        }

        if (!$oneObjectExists) {
            return array(
                'converted_parameters' => $userParameters,
            );
        }

        return array(
            'converted_parameters' => $convertedParameters,
        );
    }

    private function classExistsCheck($class)
    {
        if (!class_exists(get_class($class))) {
            throw new BlueDotRuntimeException(
                sprintf('Invalid parameter. Provided parameter object %s does not exist', get_class($class))
            );
        }
    }
}