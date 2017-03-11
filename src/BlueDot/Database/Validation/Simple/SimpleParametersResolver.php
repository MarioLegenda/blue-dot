<?php

namespace BlueDot\Database\Validation\Simple;

use BlueDot\Common\ArgumentBag;
use BlueDot\Component\TaskRunner\AbstractTask;
use BlueDot\Exception\BlueDotRuntimeException;

class SimpleParametersResolver extends AbstractTask
{
    /**
     * @throws BlueDotRuntimeException
     */
    public function doTask()
    {
        $statement = $this->arguments['statement'];
        $parameters = $this->arguments['parameters'];

        if ($statement->has('config_parameters') and !empty($parameters)) {
            $modelConverter = $this->arguments['model_converter'];
            $configParameters = $statement->get('config_parameters');

            if ($this->hasOption('array_model_parameters')) {
                $converted = $modelConverter->multipleModelsToParameters($configParameters, $parameters);

                $parameters = $converted['converted_parameters'];
            }

            if ($this->hasOption('single_model_parameter')) {
                $converted = $modelConverter->modelToParameters($configParameters, $parameters);

                $parameters = $converted['converted_parameters'];
            }

            if ($this->hasOption('parameters_exist')) {
                $this->determineStrategy($statement, $configParameters, $parameters);
            }

            $statement->add('parameters', $parameters, true);

            return;
        }

        if (!$statement->has('config_parameters') and !$statement->has('parameters')) {
            $statement->add('query_strategy', 'individual_strategy', true);
        }
    }

    private function determineStrategy(ArgumentBag $statement, array $configParameters, array $parameters)
    {
        $individualInsert = false;
        $individualMultiInsert = false;
        $multiInsert = false;

        foreach ($configParameters as $configParameter) {
            foreach ($parameters as $key => $userParameter) {
                if (!is_int($userParameter) and !is_array($userParameter) and !is_string($userParameter) and !is_null($userParameter) and !is_bool($userParameter)) {
                    throw new BlueDotRuntimeException(sprintf(
                        'Invalid user parameter type given for parameter \'%s\'. User parameters can integers, arrays, string, nulls and bool. \'%s\' given for statement \'%s\'',
                        $key,
                        gettype($userParameter),
                        $statement->get('resolved_statement_name')
                    ));
                }

                if ($configParameter === $key and !is_array($userParameter)) {
                    $individualInsert = true;
                }

                if (is_array($userParameter)) {
                    if ($individualInsert === true) {
                        throw new BlueDotRuntimeException(sprintf(
                            'If you chose to use multi insert parameters, then you cannot use individual insert parameters for statement \'%s\'',
                            $statement->get('resolved_statement_name')
                        ));
                    }

                    $firstKey = array_keys($userParameter)[0];

                    if ($configParameter === $firstKey and !is_array($userParameter[$firstKey])) {
                        $multiInsert = true;
                    }

                    if (is_int($firstKey)) {
                        $individualMultiInsert = true;
                    }

                    if ($individualMultiInsert === true) {
                        if (count($parameters) > 1) {
                            throw new BlueDotRuntimeException(sprintf(
                                'If you choose to use individual multi insert parameters, you cannot switch to individual parameters for statement \'%s\'',
                                $statement->get('resolved_statement_name')
                            ));
                        }
                    }

                    if ($multiInsert === true) {
                        if (array_key_exists($configParameter, $userParameter) === false) {
                            throw new BlueDotRuntimeException(sprintf(
                                'Config parameter \'%s\' is not provided but user parameter is for statement \'%s\'',
                                $configParameter,
                                $statement->get('resolved_statement_name')
                            ));
                        }

                        $userParamKeys = array_keys($userParameter);

                        if (!empty(array_diff($userParamKeys, $configParameters))) {
                            throw new BlueDotRuntimeException(sprintf(
                                'Invalid parameter. You provided a parameter but haven\' specified it in the configuration for statement \'%s\'',
                                $statement->get('resolved_statement_name')
                            ));
                        }
                    }
                }
            }
        }

        if ($multiInsert === true) {
            $statement->add('query_strategy', 'multi_strategy', true);
        } else if ($individualMultiInsert === true) {
            $statement->add('query_strategy', 'individual_multi_strategy', true);
        } else if ($individualInsert === true) {
            $statement->add('query_strategy', 'individual_strategy', true);
        }
    }
}