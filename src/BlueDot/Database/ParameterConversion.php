<?php

namespace BlueDot\Database;

use BlueDot\Common\ArgumentBag;
use BlueDot\Exception\BlueDotRuntimeException;

class ParameterConversion
{
    /**
     * @var ParameterConversion $instance
     */
    private static $instance;
    /**
     * @var array $userParameters
     */
    private $userParameters;
    /**
     * @var ArgumentBag $statement;
     */
    private $statement;
    /**
     * @param array $userParameters
     * @param ArgumentBag $statement
     * @return ParameterConversion
     */
    public static function instance(array $userParameters, ArgumentBag $statement) : ParameterConversion
    {
        return (self::$instance instanceof self) ? self::$instance : new self($userParameters, $statement);
    }
    /**
     * ParameterConversion constructor.
     * @param array $userParameters
     * @param ArgumentBag $statement
     */
    private function __construct(array $userParameters, ArgumentBag $statement)
    {
        $this->userParameters = $userParameters;
        $this->statement = $statement;
    }
    /**
     * @throws BlueDotRuntimeException
     */
    public function convert()
    {
        $type = $this->statement->get('type');

        if ($type === 'simple') {
            if ($this->statement->has('config_parameters')) {
                $this->validateParameters($this->statement, $this->userParameters);

                $this->statement->add('parameters', $this->userParameters, true);
            }
        } else if ($type === 'scenario') {
            $statements = $this->statement->get('statements');

            foreach ($statements as $singleStatement) {
                if ($singleStatement->has('config_parameters')) {
                    if (!array_key_exists($singleStatement->get('statement_name'), $this->userParameters)) {
                        throw new BlueDotRuntimeException('Configuration has parameters to bound but you haven\'t supplied any for '.$singleStatement->get('resolved_statement_name'));
                    }

                    $this->validateParameters($singleStatement, $this->userParameters[$singleStatement->get('statement_name')]);

                    $singleStatement->add('parameters', $this->userParameters[$singleStatement->get('statement_name')], true);
                } else if (!$singleStatement->has('config_parameters')) {
                    $singleStatement->add('query_strategy', 'individual_strategy', true);
                }

                if (!$singleStatement->has('query_strategy')) {
                    throw new BlueDotRuntimeException(sprintf(
                        'Internal error. query_strategy could not be determined for statement \'%s\'',
                        $singleStatement->get('resolved_statement_name')
                    ));
                }
            }
        }
    }

    private function validateParameters(ArgumentBag $statement, $userParameters)
    {
        $configParameters = array();
        if ($statement->has('config_parameters')) {
            $configParameters = $statement->get('config_parameters');
        }

        if (!empty($configParameters) and empty($userParameters)) {
            throw new BlueDotRuntimeException(sprintf(
                'Invalid parameters. No user parameters but config parameters were given. Config parameters are: \'%s\' for statement \'%s\'',
                implode(', ', $configParameters),
                $statement->get('resolved_statement_name')
            ));
        }

        if (empty($configParameters) and !empty($userParameters)) {
            throw new BlueDotRuntimeException(sprintf(
                'Invalid parameters. No config parameters were provided but user parameters were given for statement \'%s\'',
                $statement->get('resolved_statement_name')
            ));
        }

        if (empty($configParameters) and empty($userParameters)) {
            return null;
        }

        $individualInsert = false;
        $individualMultiInsert = false;
        $multiInsert = false;
        foreach ($configParameters as $configParameter) {
            foreach ($userParameters as $key => $userParameter) {
                if (empty($userParameter)) {
                    throw new BlueDotRuntimeException(sprintf(
                        'No user parameters but config parameters were given. Config parameters are: \'%s\' for statement \'%s\'',
                        implode(', ', $configParameters),
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
    }

    private function convertSimpleParameters(ArgumentBag $statement, $userParameters = array())
    {
        if (empty($userParameters)) {
            throw new BlueDotRuntimeException('Statement '.$statement->get('resolved_name').' has parameters in the configuration but none are provided');
        }

        $configParameters = $statement->get('parameters');

        foreach ($userParameters as $parameters) {
            if (is_array($parameters)) {
                if (!$statement->has('multi_insert')) {
                    $statement->add('multi_insert', true);

                    break;
                }
            }
        }

        $parameters = $configParameters
            ->compare($userParameters)
            ->bindValues($userParameters, $statement->has('multi_insert'));

        $statement->add('parameters', $parameters, true);
    }
}