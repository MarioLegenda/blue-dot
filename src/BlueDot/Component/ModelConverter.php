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

        $parameterType = ParameterConversion::PARAMETERS_OBJECT;

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

        if (is_null($parameterType)) {
            throw new BlueDotRuntimeException('Invalid parameters. Parameter type could not be determined. This could be an internal error. Check your parameters and fix any bugs. If everything is ok with parameters, please, contact whitepostmail@gmail.com or post an issue on Github');
        }

        return array(
            'converted_parameters' => $convertedParameters,
            'parameter_type' => $parameterType,
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
                'parameter_type' => null,
                'converted_parameters' => $userParameters,
            );
        }

        return array(
            'parameter_type' => ParameterConversion::PARAMETERS_ARRAY_OBJECT,
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