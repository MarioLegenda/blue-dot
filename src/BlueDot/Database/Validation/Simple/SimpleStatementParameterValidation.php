<?php

namespace BlueDot\Database\Validation\Simple;


use BlueDot\Common\ArgumentBag;
use BlueDot\Component\TaskRunner\AbstractTask;
use BlueDot\Exception\BlueDotRuntimeException;

class SimpleStatementParameterValidation extends AbstractTask
{
    /**
     * @void
     * @throws  BlueDotRuntimeException
     */
    public function doTask()
    {
        $statement = $this->arguments['statement'];
        $parameters = $this->arguments['parameters'];

        $this->generalParametersCheck($statement, $parameters);
        $this->singleModelParameterCheck($statement, $parameters);
        $this->modelArrayCheck($statement, $parameters);
    }

    private function modelArrayCheck(ArgumentBag $statement, $parameters = null)
    {
        if (is_array($parameters)) {
            foreach ($parameters as $parameter) {
                if (is_object($parameter)) {
                    if (!$this->hasOption('array_model_parameters')) {
                        $this->addOption('array_model_parameters', true);
                    }

                    $this->singleModelParameterCheck($statement, $parameters);
                }
            }
        }
    }

    private function singleModelParameterCheck(ArgumentBag $statement, $parameters = null)
    {
        // if the parameter is object, check if there are methods to be bound to config parameters
        if (is_object($parameters)) {
            if (!$this->hasOption('single_model_parameter')) {
                $this->addOption('single_model_parameter', true);
            }

            if (!$statement->has('config_parameters')) {
                throw new BlueDotRuntimeException(
                    sprintf('Invalid parameters. You provided a model to bind the parameters but no config parameters were provided in statement \'%s\'',
                        $statement->get('resolved_statement_name')
                    )
                );
            }

            $configParameters = $statement->get('config_parameters');

            foreach ($configParameters as $configParameter) {
                $method = 'get'.str_replace('_', '', ucwords($configParameter, '_'));

                if (!method_exists($parameters, $method)) {
                    throw new BlueDotRuntimeException(
                        sprintf('Invalid parameter. Method %s does not exist in object %s that is to be bound to config parameter %s in statement %s',
                            $method,
                            get_class($parameters),
                            $configParameter,
                            $statement->get('resolved_statement_name')
                        )
                    );
                }
            }
        }
    }

    private function generalParametersCheck(ArgumentBag $statement, $parameters = null)
    {
        // if configuration has parameters but user hasn't provided anything
        if ($statement->has('config_parameters') and empty($parameters)) {
            throw new BlueDotRuntimeException(
                sprintf(
                    'Invalid parameters. Config parameters are provided user parameters are not in statement \'%s\'',
                    $statement->get('resolved_statement_name')
                )
            );
        }

        // if user provided parameters but there are not config parameters
        if (!$statement->has('config_parameters') and !empty($parameters)) {
            throw new BlueDotRuntimeException(
                sprintf(
                    'Invalid parameters. No config parameters are provided but user parameters are in statement \'%s\'',
                    $statement->get('resolved_statement_name')
                )
            );
        }

        if ($statement->has('config_parameters') and is_array($parameters)) {
            $this->addOption('parameters_exist', true);
        }
    }
}