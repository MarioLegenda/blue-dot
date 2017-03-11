<?php

namespace BlueDot\Database\Validation\Scenario;


use BlueDot\Common\ArgumentBag;
use BlueDot\Component\TaskRunner\AbstractTask;
use BlueDot\Exception\BlueDotRuntimeException;

class ScenarioStatementParametersValidation extends AbstractTask
{
    /**
     * @var array $foreignKeyMetadata
     */
    private $foreignKeyMetadata = array();
    /**
     * @var array $useOptionsMetadata
     */
    private $useOptionsMetadata = array();
    /**
     * @var array $existsMetadata
     */
    private $existsMetadata = array();
    /**
     * @void
     */
    public function doTask()
    {
        $statement = $this->arguments['statement'];
        $parameters = $this->arguments['parameters'];
        $modelConverter = $this->arguments['model_converter'];

        $statements = $statement->get('statements');

        $this->extrapolateAndValidateMetadata($statements);
        $this->generalParametersCheck($statements, $parameters);
    }

    private function extrapolateAndValidateMetadata(ArgumentBag $statements)
    {
        foreach ($statements as $statement) {
            $resolvedStatementName = $statement->get('resolved_statement_name');

            if ($statement->has('foreign_key')) {
                $foreignKey = $statement->get('foreign_key');
                $foreignKeyResolvedName = $statement->get('scenario_name').'.'.$foreignKey->getName();

                if (!$statements->has($foreignKeyResolvedName)) {
                    throw new BlueDotRuntimeException(
                        sprintf(
                            'Invalid foreign_key. foreign_key statement %s does not exist in scenario %s',
                            $foreignKey->getName(),
                            $statement->get('scenario_name')
                        )
                    );
                }

                $foreignKeyStatement = $statements->get($foreignKeyResolvedName);

                $this->foreignKeyMetadata[$foreignKeyResolvedName]['foreign_key_usage_statements'][] = $resolvedStatementName;

                if (!array_key_exists('foreign_key_statement', $this->foreignKeyMetadata)) {
                    $this->foreignKeyMetadata['foreign_key_statement'] = $foreignKeyStatement;
                }
            }

            if ($statement->has('use_option')) {
                $useOption = $statement->get('use_option');
                $useOptionResolvedName = $statement->get('scenario_name').'.'.$useOption->getName();
                $useOptionStatement = $statements->get($useOptionResolvedName);

                if (!$statements->has($useOptionResolvedName)) {
                    throw new BlueDotRuntimeException(
                        sprintf(
                            'Invalid \'use\' option. \'use\' option statement name %s does not exist in scenario %s',
                            $useOption->getName(),
                            $statement->get('scenario_name')
                        )
                    );
                }

                $this->useOptionsMetadata[$useOptionResolvedName]['use_option_usage_statements'][] = $resolvedStatementName;

                if (!array_key_exists('use_option_statement', $this->useOptionsMetadata)) {
                    $this->useOptionsMetadata['use_option_statement'] = $useOptionStatement;
                }
            }

            if ($statement->has('if_exists') or $statement->has('if_not_exists')) {
                $existsStatementName = ($statement->has('if_exists')) ? $statement->get('if_exists') : $statement->get('if_not_exists');
                $existsStatementResolvedName = $statement->get('scenario_name').'.'.$existsStatementName;
                $existsStatement = $statements->get($existsStatementResolvedName);

                if (!$statements->has($existsStatementResolvedName)) {
                    throw new BlueDotRuntimeException(
                        sprintf(
                            'Invalid \'exists\' statement. Statement %s does not exist in scenario %s',
                            $existsStatementName,
                            $statement->get('scenario_name')
                        )
                    );
                }

                $this->existsMetadata[$existsStatementResolvedName]['exists_usage_statements'][] = $resolvedStatementName;

                if (!array_key_exists('exists_statement', $this->existsMetadata)) {
                    $this->existsMetadata['exists_statement'] = $existsStatement;
                }
            }
        }
    }

    private function generalParametersCheck(ArgumentBag $statements, $parameters = null)
    {
        foreach ($statements as $statement) {
            $statementName = $statement->get('statement_name');
            $resolvedStatementName = $statement->get('resolved_statement_name');

            if ($statement->has('config_parameters')) {
                if (!array_key_exists($statementName, $parameters)) {
                    throw new BlueDotRuntimeException(
                        sprintf(
                            'Invalid parameters. Statement %s has configuration parameters but you haven\'t supplied any in scenario %s. If you don\'t want to execute this statement, assign \'null\' to a parameter',
                            $statementName,
                            $statement->get('resolved_statement_name')
                        )
                    );
                }

                // This currently iterating statement is also a foreign key in some other statement
                // If this statement is a foreign_key in some other statement,
                // then that statement has to be executed i.e. parameters have to be provided
                // Parameters do not have to be provided only if the same foreign key statement
                // is also a if_exists or if_not_exists statement
                if (array_key_exists($resolvedStatementName, $this->foreignKeyMetadata)) {
                    $userParameter = $parameters[$statementName];

                    if ($userParameter === null) {
                        $parentStatements = $this->foreignKeyMetadata[$resolvedStatementName]['foreign_key_usage_statements'];

                        foreach ($parentStatements as $holderStatementResolvedName) {
                            $holderStatement = $statements->get($holderStatementResolvedName);

                            if (!$holderStatement->has('if_exists') and !$holderStatement->has('if_not_exists')) {
                                throw new BlueDotRuntimeException(
                                    sprintf(
                                        'Invalid foreign_key. Parameters for a foreign_key statement cannot be null unless an if_exists or if_not_exists option is present. Invalid foreign_key statement is %s that is in scenario %s',
                                        $resolvedStatementName,
                                        $holderStatementResolvedName
                                    )
                                );
                            }

                            $statement->add('has_to_execute', false, true);
                        }
                    }
                }

                if (array_key_exists($resolvedStatementName, $this->useOptionsMetadata)) {
                    $userParameter = $parameters[$statementName];

                    if ($userParameter === null) {
                        $parentStatements = $this->foreignKeyMetadata[$resolvedStatementName]['foreign_key_usage_statements'];

                        foreach ($parentStatements as $holderStatementResolvedName) {
                            $holderStatement = $statements->get($holderStatementResolvedName);

                            if (!$holderStatement->has('if_exists') and !$holderStatement->has('if_not_exists')) {
                                throw new BlueDotRuntimeException(
                                    sprintf(
                                        'Invalid \'use\' option. Parameters for a \'use\' option statement cannot be null unless an if_exists or if_not_exists option is present. Invalid \'use\' option is %s in scenario %s',
                                        $resolvedStatementName,
                                        $holderStatementResolvedName
                                    )
                                );
                            }
                        }

                        $statement->add('has_to_execute', false, true);
                    }
                }

                if (!array_key_exists($statementName, $parameters)) {
                    throw new BlueDotRuntimeException(
                        sprintf(
                            'Invalid parameters. Statement %s has configuration parameters but you haven\'t supplied any in scenario %s',
                            $statementName,
                            $statement->get('resolved_statement_name')
                        )
                    );
                }
            }
        }
    }
}