<?php

namespace BlueDot\Database;

use BlueDot\Common\ArgumentBag;
use BlueDot\Component\ModelConverter;
use BlueDot\Exception\BlueDotRuntimeException;

/**
 * Class ParameterConversion
 * @package BlueDot\Database
 *
 * Rules for validation:
 *
 * Simple:
 *
 * - All simple statements have to be validated
 *
 * Scenario:
 *
 * - a scenario has to have at least one statement
 *     - if that statement has option 'can_be_empty_result' and the statement returns an empty result,
 *       that statement is regarded as success
 *     - if the statement does not have option 'can_be_empty_result', and statement returns an empty
 *       result, statement is regarded as failure
 *
 * - if user does not provide the statement in execute function, that statement will not be executed
 *      - statement has to be provided in these circumstances
 *           1. If that statement is a foreign key in some other statement
 *           2  If that statement is a use option in some other statement
 *      - if the statement has the option 'can_be_empty_result', it will regard itself as an empty result
 *
 */
class ParameterConversion
{
    const PARAMETERS_ARRAY = 1;
    const PARAMETERS_OBJECT = 2;
    const PARAMETERS_ARRAY_OBJECT = 4;

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

    private $parameterType = null;
    /**
     * @param array $userParameters
     * @param ArgumentBag $statement
     * @return ParameterConversion
     */
    public static function instance($userParameters, ArgumentBag $statement) : ParameterConversion
    {
        return (self::$instance instanceof self) ? self::$instance : new self($userParameters, $statement);
    }
    /**
     * ParameterConversion constructor.
     * @param array $userParameters
     * @param ArgumentBag $statement
     */
    private function __construct($userParameters, ArgumentBag $statement)
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

            $this->statement->add('query_strategy', 'individual_strategy', true);

            $this->validateParameters($this->statement, $this->userParameters, 'simple');

            if ($this->statement->has('config_parameters')) {
                $this->statement->add('parameters', $this->userParameters, true);
            }
        } else if ($type === 'scenario') {
            $statements = $this->statement->get('statements');

            $foreignKeys = array();
            $useStatements = array();
            foreach ($statements as $statement) {
                $statement->add('query_strategy', 'individual_strategy', true);

                if ($statement->has('foreign_key')) {
                    $foreignKeys[$statement->get('foreign_key')->getName()][] = $statement->get('resolved_statement_name');
                }

                if ($statement->has('use_option')) {
                    $useStatements[$statement->get('use_option')->getName()] = $statement->get('resolved_statement_name');
                }
            }

            foreach ($statements as $singleStatement) {
                $canBeEmptyResult = $singleStatement->get('can_be_empty_result');

                if ($canBeEmptyResult === true) {
                    if (array_key_exists($singleStatement->get('statement_name'), $foreignKeys)) {
                        throw new BlueDotRuntimeException(sprintf(
                            'Invalid usage of \'%s\' option. \'%s\' option cannot be used in statements that are used as \'%s\' or \'%s\' options in other statements. Statement \'%s\' is used as \'foreign_key\' in \'%s\'',
                            'can_be_empty_result',
                            'can_be_empty_result',
                            'use',
                            'foreign_key',
                            $singleStatement->get('resolved_statement_name'),
                            implode(', ', $foreignKeys[$singleStatement->get('statement_name')])
                        ));
                    }

                    if (array_key_exists($singleStatement->get('statement_name'), $useStatements)) {
                        throw new BlueDotRuntimeException(sprintf(
                            'Invalid usage of \'%s\' option. \'%s\' option cannot be used in statements that are used as \'%s\' or \'%s\' options in other statements. Statement \'%s\' is used as \'use\' in \'%s\'',
                            'can_be_empty_result',
                            'can_be_empty_result',
                            'use',
                            'foreign_key',
                            $singleStatement->get('resolved_statement_name'),
                            implode(', ', $foreignKeys[$singleStatement->get('statement_name')])
                        ));
                    }
                }

                if (is_array($this->userParameters)) {
                    if (array_key_exists($singleStatement->get('statement_name'), $this->userParameters)) {
                        if ($this->userParameters[$singleStatement->get('statement_name')] === null) {
                            if (array_key_exists($singleStatement->get('statement_name'), $foreignKeys)) {
                                $statementName = $singleStatement->get('statement_name');
                                $holderResolvedStatements = $foreignKeys[$statementName];

                                foreach ($holderResolvedStatements as $holderResolvedStatement) {
                                    $ifExistsStatement = $statements->get($holderResolvedStatement);

                                    if (!$ifExistsStatement->has('if_exists')) {
                                        throw new BlueDotRuntimeException(sprintf(
                                            'Invalid statement. Statement \'%s\' has to be executed because it exists as a \'foreign_key\' in statement(s) \'%s\'',
                                            $singleStatement->get('resolved_statement_name'),
                                            implode(', ', $foreignKeys[$singleStatement->get('statement_name')])
                                        ));
                                    }
                                }
                            }

                            $singleStatement->add('has_to_execute', false, true);

                            continue;
                        }
                    }
                }

                if ($singleStatement->has('config_parameters')) {
                    if (!array_key_exists($singleStatement->get('statement_name'), $this->userParameters)) {
                        throw new BlueDotRuntimeException('Configuration has parameters to bound but you haven\'t supplied any for '.$singleStatement->get('resolved_statement_name'));
                    }

                    $this->validateParameters($singleStatement, $this->userParameters[$singleStatement->get('statement_name')], 'scenario');

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

    private function validateParameters(ArgumentBag $statement, $userParameters, $statementType = null)
    {
        $this->parameterType = null;
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

        if ($statementType === 'simple') {
            $userParameters = $this->convertSimpleObjectParameters($configParameters, $userParameters);
        }

        if ($statementType === 'scenario') {
            //$userParameters = $this->convertScenarioObjectParameters($configParameters, $userParameters);
        }

        $individualInsert = false;
        $individualMultiInsert = false;
        $multiInsert = false;
        foreach ($configParameters as $configParameter) {
            foreach ($userParameters as $key => $userParameter) {
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

            if ($this->parameterType === ParameterConversion::PARAMETERS_OBJECT) {
                $this->userParameters = $userParameters;
            } else if ($this->parameterType === ParameterConversion::PARAMETERS_ARRAY_OBJECT) {
                $this->userParameters = $userParameters;
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

    private function convertSimpleObjectParameters(array $configParameters, $userParameters)
    {
        $modelConverted = new ModelConverter();

        if (is_object($userParameters)) {
            $convertedData = $modelConverted->modelToParameters($configParameters, $userParameters);

            $this->parameterType = $convertedData['parameter_type'];

            return $convertedData['converted_parameters'];
        }

        if (is_array($userParameters)) {
            $convertedData = $modelConverted->multipleModelsToParameters($configParameters, $userParameters);

            $this->parameterType = $convertedData['parameter_type'];

            return $convertedData['converted_parameters'];
        }
    }

    private function convertScenarioObjectParameters(array $configParameters, $userParameters) : array
    {
        $convertedParameters = array();

        foreach ($userParameters as $scenarioName => $userParameter) {
            if (is_object($userParameter)) {
                $convertedParameters[$scenarioName] = $this->convertSimpleObjectParameters($configParameters, $userParameter);
            }
        }

        return $convertedParameters;
    }
}