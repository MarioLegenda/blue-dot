<?php

namespace BlueDot\Common;

use BlueDot\Database\ParameterConversion;
use BlueDot\Exception\CommonInternalException;
use BlueDot\Exception\ConfigurationException;
use BlueDot\Exception\QueryException;

class StatementValidator
{
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * @var ArgumentValidator $argumentValidator
     */
    private $argumentValidator;
    /**
     * @var array $configuration
     */
    private $configuration;
    /**
     * @var ParameterConversion $parameterConversion
     */
    private $parameterConversion;
    /**
     * @param ArgumentValidator $validator
     * @param array $configuration
     * @param ParameterConversion $parameterConversion
     */
    public function __construct(ArgumentValidator $validator, array $configuration, ParameterConversion $parameterConversion)
    {
        $this->argumentValidator = $validator;
        $this->configuration = $configuration;
        $this->parameterConversion = $parameterConversion;
    }
    /**
     * @return StatementValidator
     * @throws CommonInternalException
     * @throws QueryException
     */
    public function validate() : StatementValidator
    {
        $this->argumentValidator->validate();

        $type = $this->argumentValidator->getType();

        if (!array_key_exists($type, $this->configuration)) {
            throw new CommonInternalException('Invalid input. \''.$this->argumentValidator->getResolvedName().'\' does not exist');
        }

        $statementType = $this->configuration[$type];

        if (!$statementType->has($this->argumentValidator->getResolvedName())) {
            throw new CommonInternalException('Invalid input. \''.$this->argumentValidator->getResolvedName().'\' does not exist');
        }

        $statement = $this->configuration[$type]->get($this->argumentValidator->getResolvedName());

        if ($statement->get('type') === 'scenario') {
            $this->generalValidation($statement);
            $this->validateUseOptions($statement->get('statements'));
            $this->validateReturnData($statement);
        }

        $this->parameterConversion->convert($statement->get('type'), $statement);

        $this->statement = $statement;

        return $this;
    }
    /**
     * @return ArgumentBag
     */
    public function getStatement() : ArgumentBag
    {
        return $this->statement;
    }

    private function generalValidation(ArgumentBag $mainStatement)
    {
        $hasSelectStatement = false;
        $statements = $mainStatement->get('statements');
        foreach ($statements as $statement) {
            $sqlType = $statement->get('sql_type');

            if ($sqlType === 'select') {
                $hasSelectStatement = true;
            }
        }

        if ($mainStatement->has('rules')) {
            $rules = $mainStatement->get('rules');

            if ($rules->has('minimal_select_statement')) {
                $minimalSelectStatement = $rules->getRule('minimal_select_statement');

                if ($minimalSelectStatement === true) {
                    if ($hasSelectStatement === false) {
                        throw new CommonInternalException('A scenario should have at least one \'select\' sql query. Found none in '.$mainStatement->get('root_config')->get('scenario_name'));
                    }
                }
            }
        }
    }

    private function validateReturnData(ArgumentBag $mainStatement)
    {
        $rootConfig = $mainStatement->get('root_config');
        $returnEntities = $rootConfig->get('return_entity')->getAllReturnData();
        $scenarioName = $rootConfig->get('scenario_name');
        $statements = $mainStatement->get('statements');

        if ($rootConfig->has('rules')) {
            $rules = $rootConfig->get('rules');

            $rule = $rules->getRule('return_entity');

            if ($rule === false) {
                return;
            }
        }

        foreach ($returnEntities as $returnEntity) {
            $scenarioStatementName = 'scenario.'.$scenarioName.'.'.$returnEntity->getStatementName();

            if (!$statements->has($scenarioStatementName)) {
                throw new ConfigurationException('Scenario statement name provided in the \'return_entity\' configuration value for '.$scenarioStatementName.' does not exist');
            }
        }
    }

    private function validateUseOptions(ArgumentBag $statements)
    {
        foreach ($statements as $statement) {
            if ($statement->has('use_option')) {
                $useOption = $statement->get('use_option');
                $useOptionStatementName = $statement->get('scenario_name').'.'.$useOption->getName();

                if (!$statements->has($useOptionStatementName)) {
                    throw new ConfigurationException('\''.$useOption->getName().'\' not found in '.$statement->get('resolved_statement_name'));
                }

                $sqlType = $statement->get('sql_type');

                if ($sqlType === 'delete' or $sqlType === 'insert' or $sqlType === 'update') {
                    $useOptionStatement = $statements->get($useOptionStatementName);

                    if ($useOptionStatement->get('sql_type') !== 'select') {
                        throw new ConfigurationException('\'use\' option statement has to be a select \'sql_type\'');
                    }
                }
            }
        }
    }
}