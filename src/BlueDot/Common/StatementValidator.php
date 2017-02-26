<?php

namespace BlueDot\Common;

use BlueDot\Exception\CompileException;
use BlueDot\Exception\ConfigurationException;

class StatementValidator implements ValidatorInterface
{
    /**
     * @var ArgumentBag $statement
     */
    private $statement;
    /**
     * @param mixed $validationArgument
     * @throws CompileException
     * @return ValidatorInterface
     */
    public function setValidationArgument($validationArgument) : ValidatorInterface
    {
        if (!$validationArgument instanceof ArgumentBag) {
            throw new CompileException('Invalid argument in '.StatementValidator::class.'. This is probably a bug so please, contact whitepostmail@gmail.com or post an issue');
        }

        $this->statement = $validationArgument;

        return $this;
    }
    /**
     * @return ValidatorInterface
     */
    public function validate() : ValidatorInterface
    {
        if ($this->statement->get('type') === 'scenario') {
            $this->generalValidation($this->statement);
            $this->validateUseOptions($this->statement->get('statements'));
            $this->validateReturnData($this->statement);
        }

        return $this;
    }

    private function generalValidation(ArgumentBag $mainStatement)
    {
        $hasSelectStatement = false;
        $statements = $mainStatement->get('statements');
        foreach ($statements as $statement) {
            $sqlType = $statement->get('statement_type');

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
                        throw new ConfigurationException('A scenario should have at least one \'select\' sql query. Found none in '.$mainStatement->get('root_config')->get('scenario_name'));
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

                $sqlType = $statement->get('statement_type');

                if ($sqlType === 'delete' or $sqlType === 'insert' or $sqlType === 'update') {
                    $useOptionStatement = $statements->get($useOptionStatementName);

                    if ($useOptionStatement->get('statement_type') !== 'select') {
                        throw new ConfigurationException('\'use\' option statement has to be a select \'sql_type\'');
                    }
                }
            }
        }
    }
}