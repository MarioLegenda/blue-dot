<?php

namespace BlueDot\Common;

use BlueDot\Database\Model\ConfigurationInterface;
use BlueDot\Database\Model\Simple\SimpleConfiguration;
use BlueDot\Exception\ConfigurationException;

class StatementValidator implements ValidatorInterface
{
    /**
     * @param ConfigurationInterface|SimpleConfiguration $configuration
     * @return ValidatorInterface
     * @throws ConfigurationException
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function validate($configuration) : ValidatorInterface
    {
        if ($configuration->getMetadata()->getType() === 'scenario') {
            $this->generalValidation($statement);
            $this->validateUseOptions($statement->get('statements'));
            $this->validateReturnData($statement);
        }

        return $this;
    }
    /**
     * @param ArgumentBag $mainStatement
     * @throws ConfigurationException
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
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
    /**
     * @param ArgumentBag $mainStatement
     * @throws ConfigurationException
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    private function validateReturnData(ArgumentBag $mainStatement)
    {
        $rootConfig = $mainStatement->get('root_config');
        $scenarioName = $rootConfig->get('scenario_name');
        $statements = $mainStatement->get('statements');

        if ($rootConfig->has('rules')) {
            $rules = $rootConfig->get('rules');

            $rule = $rules->getRule('return_entity');

            if ($rule === false) {
                return;
            }
        }

        if ($rootConfig->has('return_data')) {
            $returnEntities = $rootConfig->get('return_data')->getAllReturnData();

            foreach ($returnEntities as $returnEntity) {
                $scenarioStatementName = 'scenario.'.$scenarioName.'.'.$returnEntity->getStatementName();

                if (!$statements->has($scenarioStatementName)) {
                    throw new ConfigurationException('Scenario statement name provided in the \'return_entity\' configuration value for '.$scenarioStatementName.' does not exist');
                }
            }
        }
    }
    /**
     * @param ArgumentBag $statements
     * @throws ConfigurationException
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
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