<?php

namespace BlueDot\Database\Validation\Scenario;

use BlueDot\Common\ArgumentBag;
use BlueDot\Component\TaskRunner\AbstractTask;
use BlueDot\Exception\BlueDotRuntimeException;

class ScenarioParametersResolver extends AbstractTask
{
    /**
     * @throws BlueDotRuntimeException
     */
    public function doTask()
    {
        $statement = $this->arguments['statement'];
        $parameters = $this->arguments['parameters'];

        $statements = $statement->get('statements');

        /** @var ArgumentBag $statement */
        foreach ($statements as $statement) {
            if ($statement->has('config_parameters')) {
                $configParameters = $statement->get('config_parameters');
                $userParameters = $parameters[$statement->get('statement_name')];

                if (!$statement->has('has_to_execute')) {
                    $this->determineStrategy($statement, $configParameters, $userParameters);

                }
            } else if (!$statement->has('config_parameters')) {
                if (!$statement->has('query_strategy')) {
                    $statement->add('query_strategy', 'individual_strategy');
                }
            }
        }
    }
    /**
     * @param ArgumentBag $statement
     * @param array $configParameters
     * @param $userParameters
     * @throws BlueDotRuntimeException
     */
    private function determineStrategy(ArgumentBag $statement, array $configParameters, $userParameters)
    {
        $individualInsert = false;
        $individualMultiInsert = false;
        $multiInsert = false;
        foreach ($configParameters as $configParameter) {
            foreach ($userParameters as $key => $userParameter) {
                if (!is_int($userParameter) and !is_array($userParameter) and !is_string($userParameter) and !is_null($userParameter) and !is_bool($userParameter)) {
                    throw new BlueDotRuntimeException(sprintf(
                        'Invalid user parameter type given for parameter \'%s\'. User parameters can be integers, arrays, string, nulls and bool. \'%s\' given for statement \'%s\'',
                        $key,
                        gettype($userParameter),
                        $statement->get('resolved_statement_name')
                    ));
                }

                if ($configParameter === $key and !is_array($userParameter)) {
                    $individualInsert = true;
                }

                if (is_array($userParameter)) {
                    if (empty($userParameter)) {
                        throw new BlueDotRuntimeException(
                            sprintf(
                                'Invalid parameters. You specified a \'%s\' configuration parameter but provided an empty array for statement %s',
                                $configParameter,
                                $statement->get('resolved_statement_name')
                            )
                        );
                    }

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
                        if (count($userParameters) > 1) {
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

        if (!$statement->has('query_strategy')) {
            throw new BlueDotRuntimeException(
                sprintf(
                    'Invalid query strategy. Query strategy for %s could not been determined. This is a bug. Please, contact whitepostmail@gmail.com or post an issue on Github',
                    $statement->get('resolved_statement_name')
                )
            );
        }

        $statement->add('parameters', $userParameters, true);
    }
}