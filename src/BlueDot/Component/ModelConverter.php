<?php

namespace BlueDot\Component;

use BlueDot\Entity\Model;
use BlueDot\Exception\BlueDotRuntimeException;

class ModelConverter
{
    /**
     * @var Model $model
     */
    private $model;
    /**
     * ModelConverter constructor.
     * @param Model|null $model
     */
    public function __construct(Model $model = null)
    {
        $this->model = $model;
    }
    /**
     * @param array $configParameters
     * @param $parameters
     * @throws BlueDotRuntimeException
     */
    public function validateSingleModel(array $configParameters, $parameters)
    {
        $class = get_class($parameters);

        if ($this->model->getName() !== $class) {
            throw new BlueDotRuntimeException(
                sprintf(
                    'Invalid model given. Configuration model is %s but you provided %s',
                    $this->model->getName(),
                    $class
                )
            );
        }
    }
    /**
     * @param array $configParameters
     * @param $userParameters
     * @return array
     * @throws BlueDotRuntimeException
     */
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
    /**
     * @param array $configParameters
     * @param $userParameters
     * @return array
     */
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