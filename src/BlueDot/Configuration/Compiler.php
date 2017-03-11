<?php

namespace BlueDot\Configuration;

use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Entity\Model;
use BlueDot\Exception\CompileException;

use BlueDot\Common\{ ArgumentBag, ArgumentValidator, ValidatorInterface};
use BlueDot\Database\Parameter\{ Parameter, ParameterCollection };
use BlueDot\Database\Scenario\{ UseOption, ForeignKey, ScenarioReturnEntity, Rules };

class Compiler
{
    /**
     * @var ArgumentValidator $argumentValidator
     */
    private $argumentValidator;
    /**
     * @var ValidatorInterface|StatementValidator $statementValidator
     */
    private $statementValidator;
    /**
     * @var array $configuration
     */
    private $configuration;
    /**
     * @var ConfigurationValidator $configurationValidator
     */
    private $configurationValidator;
    /**
     * @var array $builtConfiguration
     */
    private $builtConfiguration = array();
    /**
     * Compiler constructor.
     * @param array $configuration
     * @param ValidatorInterface $argumentValidator
     * @param ValidatorInterface $statementValidator
     * @param ConfigurationValidator $configurationValidator
     */
    public function __construct(
        array $configuration,
        ValidatorInterface
        $argumentValidator,
        ValidatorInterface $statementValidator,
        ConfigurationValidator $configurationValidator
    )
    {
        $this->configuration = $configuration;
        $this->argumentValidator = $argumentValidator;
        $this->statementValidator = $statementValidator;
        $this->configurationValidator = $configurationValidator;

        if (array_key_exists('simple', $configuration)) {
            $this->builtConfiguration['simple'] = new ArgumentBag();
        }

        if (array_key_exists('scenario', $configuration)) {
            $this->builtConfiguration['scenario'] = new ArgumentBag();
        }

        if (array_key_exists('callable', $configuration)) {
            $this->builtConfiguration['callable'] = new ArgumentBag();
        }
    }
    /**
     * @param string $name
     * @return ArgumentBag
     * @throws CompileException
     */
    public function compile(string $name) : ArgumentBag
    {
        $this->configurationValidator->validate();

        $this->argumentValidator->setValidationArgument($name)->validate();

        $type = $this->argumentValidator->getType();

        if ($this->builtConfiguration[$type]->has($name)) {
            return $this->builtConfiguration[$type]->get($name);
        }

        $method = 'compile'.ucfirst($type).'Statement';

        if (!method_exists($this, $method)) {
            throw new CompileException($name.' statement not found');
        }

        $foundConfig = $this->$method($name);

        if ($foundConfig === false) {
            throw new CompileException($name.' statement not found');
        }

        $statement = $this->builtConfiguration[$type]->get($name);

        $this->statementValidator->setValidationArgument($statement)->validate();

        return $statement;
    }

    private function compileSimpleStatement(string $name) : bool
    {
        $builtSimpleConfiguration = $this->builtConfiguration['simple'];

        $foundConfig = false;

        foreach ($this->configuration['simple'] as $type => $typeConfig) {
            foreach ($typeConfig as $statementName => $statementConfig) {
                $resolvedName = $type.'.'.$statementName;

                if ('simple.'.$resolvedName === $name) {
                    $builtStatement = new ArgumentBag();
                    $builtStatement
                        ->add('type', 'simple')
                        ->add('resolved_name', $resolvedName)
                        ->add('statement_type', $type)
                        ->add('statement_name', $statementName)
                        ->add('resolved_statement_name', 'simple.'.$resolvedName);

                    $workConfig = new ArgumentBag();
                    $workConfig->add('sql', $statementConfig['sql']);

                    if (array_key_exists('parameters', $statementConfig)) {
                        $parameters = $statementConfig['parameters'];

                        $workConfig->add('config_parameters', $parameters);
                    }

                    if (array_key_exists('model', $statementConfig)) {
                        $object = $statementConfig['model']['object'];
                        $properties = (array_key_exists('properties', $statementConfig['model'])) ? $statementConfig['model']['properties'] : array();

                        if (!class_exists($object)) {
                            throw new CompileException(sprintf('Invalid model options. Object \'%s\' does not exist', $object));
                        }

                        if (!empty($properties)) {
                            foreach ($properties as $key => $value) {
                                if (!is_string($key)) {
                                    throw new CompileException('Invalid model options. \'properties\' should be a associative array. %s given for value %s', $key, $value);
                                }
                            }
                        }

                        $workConfig->add('model', new Model($object, $properties));
                    }

                    $builtStatement->mergeStorage($workConfig);

                    $builtSimpleConfiguration->add('simple.'.$builtStatement->get('resolved_name'), $builtStatement);

                    $foundConfig = true;

                    break;
                }
            }

            if ($foundConfig === true) {
                break;
            }
        }

        return $foundConfig;
    }

    private function compileScenarioStatement(string $name) : bool
    {
        $mainScenario = $this->builtConfiguration['scenario'];

        $scenarioConfiguration = $this->configuration['scenario'];

        $foundConfig = false;

        foreach ($scenarioConfiguration as $scenarioName => $scenarioConfigs) {
            $scenarioStatements = $scenarioConfigs['statements'];
            $resolvedScenarioName = 'scenario.'.$scenarioName;

            if ($name === $resolvedScenarioName) {

                $builtScenarioConfiguration = new ArgumentBag();
                $builtScenarioConfiguration->add('type', 'scenario');

                $rootConfig = new ArgumentBag();
                $rootConfig
                    ->add('atomic', $scenarioConfigs['atomic'])
                    ->add('scenario_name', $scenarioName);

                if (array_key_exists('return_data', $scenarioConfigs)) {
                    if (empty($scenarioConfigs['return_data'])) {
                        throw new CompileException(
                            sprintf('Invalid configuration. If provided, \'return_data\' has to be a non empty array')
                        );
                    }

                    $rootConfig->add('return_data', new ScenarioReturnEntity($scenarioConfigs['return_data']));
                }

                $statemens = new ArgumentBag();
                foreach ($scenarioStatements as $statementName => $statementConfig) {
                    $resolvedStatementName = 'scenario.'.$scenarioName.'.'.$statementName;

                    $scenarioStatement = new ArgumentBag();
                    $scenarioStatement
                        ->add('scenario_name', $resolvedScenarioName)
                        ->add('resolved_statement_name', $resolvedStatementName)
                        ->add('statement_name', $statementName)
                        ->add('sql', $statementConfig['sql']);

                    $sql = $scenarioStatement->get('sql');

                    preg_match('#(\w+\s)#i', $sql, $matches);

                    if (empty($matches)) {
                        throw new CompileException(sprintf(
                            'Sql syntax could not be determined for statement %s. Sql: %s',
                            $resolvedStatementName,
                            $sql
                        ));
                    }

                    $sqlType = trim(strtolower($matches[1]));

                    if ($sqlType === 'create' or $sqlType === 'use' or $sqlType === 'drop') {
                        $sqlType = 'table';
                    }

                    $scenarioStatement->add('statement_type', $sqlType);

                    $scenarioStatement->add('can_be_empty_result', false);

                    if (array_key_exists('if_exists', $statementConfig)) {
                        $scenarioStatement->add('if_exists', $statementConfig['if_exists']);
                    }

                    if (array_key_exists('if_not_exists', $statementConfig)) {
                        $scenarioStatement->add('if_not_exists', $statementConfig['if_exists']);
                    }

                    if (array_key_exists('can_be_empty_result', $statementConfig)) {
                        $scenarioStatement->add('can_be_empty_result', $statementConfig['can_be_empty_result'], true);
                    }

                    if (array_key_exists('parameters', $statementConfig)) {
                        $parameters = $statementConfig['parameters'];

                        $scenarioStatement->add('config_parameters', $parameters);
                    }

                    if (array_key_exists('use', $statementConfig)) {
                        $useOption = $statementConfig['use'];

                        $scenarioStatement->add(
                            'use_option',
                            new UseOption($useOption['statement_name'], $useOption['values'])
                        );
                    }

                    if (array_key_exists('foreign_key', $statementConfig)) {
                        $foreignKey = $statementConfig['foreign_key'];

                        $scenarioStatement->add(
                            'foreign_key',
                            new ForeignKey(
                                $foreignKey['statement_name'],
                                $foreignKey['bind_to']
                            )
                        );
                    }

                    $statemens->add($resolvedStatementName, $scenarioStatement);
                }

                $builtScenarioConfiguration->add('root_config', $rootConfig);
                $builtScenarioConfiguration->add('statements', $statemens);

                $mainScenario->add($resolvedScenarioName, $builtScenarioConfiguration);

                $foundConfig = true;

                break;
            }

            if ($foundConfig === true) {
                break;
            }
        }

        return $foundConfig;
    }

    private function compileCallableStatement(string $name) : bool
    {
        $callableConfig = $this->builtConfiguration['callable'];
        $callableConfig->add('type', 'callable', true);

        $foundConfig = false;

        foreach ($this->configuration['callable'] as $key => $config) {
            $resolvedName = 'callable.'.$key;

            if ($resolvedName === $name) {
                $subConfig = new ArgumentBag();

                $subConfig
                    ->add('type', 'callable')
                    ->add('data_type', $config['type'])
                    ->add('name', $config['name']);

                $callableConfig->add('callable.'.$key, $subConfig);

                $foundConfig = true;

                break;
            }
        }

        return $foundConfig;
    }

    private function addSimpleParameters(array $parameters) : ParameterCollection
    {
        $parameterCollection = new ParameterCollection();

        foreach ($parameters as $parameter) {
            $parameterCollection->addParameter(new Parameter($parameter));
        }

        return $parameterCollection;
    }

    private function addScenarioParameters(array $parameters) : ParameterCollection
    {
        $parameterCollection = new ParameterCollection();

        foreach ($parameters as $key => $parameter) {
            $parameterCollection = new ParameterCollection();

            foreach ($parameters as $parameter) {
                $parameterCollection->addParameter(new Parameter($parameter));
            }
        }

        return $parameterCollection;
    }
}