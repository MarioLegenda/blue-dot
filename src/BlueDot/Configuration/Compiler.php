<?php

namespace BlueDot\Configuration;

use BlueDot\Configuration\Flow\SimpleFlow;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Import\SqlImport;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Exception\CompileException;
use BlueDot\Common\StatementValidator;

use BlueDot\Common\{
    ArgumentBag, FlowProductInterface, Util\Util, ValidatorInterface
};
use BlueDot\Database\Scenario\{ UseOption, ForeignKey, ScenarioReturnEntity };
use BlueDot\Exception\ConfigurationException;

class Compiler
{
    /**
     * @var ValidatorInterface|StatementValidator $statementValidator
     */
    private $statementValidator;
    /**
     * @var ValidatorInterface|StatementValidator $argumentValidator
     */
    private $argumentValidator;
    /**
     * @var array $configuration
     */
    private $configuration;
    /**
     * @var ImportCollection $imports
     */
    private $imports;
    /**
     * @var ConfigurationValidator $configurationValidator
     */
    private $configurationValidator;
    /**
     * @var array $builtStatements
     */
    private $builtStatements = [];
    /**
     * @var ConfigurationCollection $configurationCollection
     */
    private $configurationCollection;
    /**
     * @var bool $isCompiled
     */
    private $isCompiled = false;
    /**
     * Compiler constructor.
     * @param string $configSource
     * @param array $configuration
     * @param ValidatorInterface $argumentValidator
     * @param ValidatorInterface $statementValidator
     * @param ConfigurationValidator $configurationValidator
     * @param ImportCollection $imports
     * @throws BlueDotRuntimeException
     * @throws CompileException
     * @throws ConfigurationException
     */
    public function __construct(
        string $configSource,
        array $configuration,
        ValidatorInterface $argumentValidator,
        ValidatorInterface $statementValidator,
        ConfigurationValidator $configurationValidator,
        ImportCollection $imports
    ) {
        $this->configuration = $configuration;
        $this->argumentValidator = $argumentValidator;
        $this->statementValidator = $statementValidator;
        $this->configurationValidator = $configurationValidator;
        $this->imports = $imports;

        $this->configurationValidator->validate();

        if (array_key_exists('sql_import', $configuration)) {
            $path = $this->validateImport($configSource, $configuration['sql_import']);

            $this->imports->addImport(new SqlImport($path));
        }

        $this->compileReal();
        $this->markCompiled();
    }
    /**
     * @param string $name
     * @return FlowProductInterface
     */
    public function compile(string $name) : FlowProductInterface
    {
        $this->argumentValidator->validate($name);

        if ($this->configurationCollection->hasConfiguration($name)) {
            return $this->configurationCollection->getConfiguration($name);
        }

        throw new \RuntimeException(sprintf('Statement \'%s\' not found', $name));
    }
    /**
     * @return bool
     */
    public function isCompiled(): bool
    {
        return $this->isCompiled;
    }
    /**
     * @throws BlueDotRuntimeException
     * @throws CompileException
     * @throws ConfigurationException
     */
    private function compileReal()
    {
        $this->compileSimpleStatements();
        $this->compileScenarioStatement();
        $this->compileCallableStatement();

        $this->configurationCollection = new ConfigurationCollection(
            Util::instance()->createGenerator($this->builtStatements)
        );
    }
    /**
     * @throws BlueDotRuntimeException
     * @throws CompileException
     */
    private function compileSimpleStatements()
    {
        if (!array_key_exists('simple', $this->configuration)) {
            return null;
        }

        $simpleConfigurationGenerator = Util::instance()->createGenerator($this->configuration['simple']);

        $simpleFlow = new SimpleFlow();

        foreach ($simpleConfigurationGenerator as $typeConfig) {
            foreach ($typeConfig['item'] as $statementName => $statementConfig) {
                $resolvedStatementName = sprintf('simple.%s.%s', $typeConfig['key'], $statementName);

                $configuration = $simpleFlow->create(
                    $resolvedStatementName,
                    $statementConfig,
                    $this->imports
                );

                $this->builtStatements[$resolvedStatementName] = $configuration;
            }
        }
    }
    /**
     * @throws BlueDotRuntimeException
     * @throws CompileException
     * @throws ConfigurationException
     */
    private function compileScenarioStatement()
    {
        if (!array_key_exists('scenario', $this->configuration)) {
            return null;
        }

        $scenarioConfiguration = $this->configuration['scenario'];

        foreach ($scenarioConfiguration as $scenarioName => $scenarioConfigs) {
            $scenarioStatements = $scenarioConfigs['statements'];
            $resolvedScenarioName = 'scenario.'.$scenarioName;

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

                if ($this->imports->hasImport('sql_import')) {
                    $possibleImport = $statementConfig['sql'];
                    $import = $this->imports->getImport('sql_import');

                    if ($import->hasValue($possibleImport)) {
                        $scenarioStatement->add('sql', $import->getValue($possibleImport), true);
                    }
                }

                $sql = $scenarioStatement->get('sql');

                preg_match('#(\w+\s)#i', $sql, $matches);

                if (empty($matches)) {
                    throw new CompileException(sprintf(
                        'Sql syntax could not be determined for statement %s. Sql: %s. This could be because you use sql_import and misspelled this one',
                        $resolvedStatementName,
                        $sql
                    ));
                }

                $sqlType = trim(strtolower($matches[1]));

                if ($sqlType === 'create' or $sqlType === 'use' or $sqlType === 'drop') {
                    $sqlType = 'table';
                }

                if ($sqlType === 'modify' or $sqlType === 'alter') {
                    $sqlType = 'update';
                }

                $scenarioStatement->add('statement_type', $sqlType);

                $scenarioStatement->add('can_be_empty_result', false);

                if (array_key_exists('if_exists', $statementConfig)) {
                    $scenarioStatement->add('if_exists', $statementConfig['if_exists']);
                }

                if (array_key_exists('if_not_exists', $statementConfig)) {
                    $scenarioStatement->add('if_not_exists', $statementConfig['if_not_exists']);
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

            $this->builtStatements[$resolvedScenarioName] = $builtScenarioConfiguration;
        }
    }
    /**
     * @return null
     * @throws BlueDotRuntimeException
     */
    private function compileCallableStatement()
    {
        if (!array_key_exists('callable', $this->configuration)) {
            return null;
        }

        foreach ($this->configuration['callable'] as $key => $config) {
            $callableConfig = new ArgumentBag();
            $callableConfig->add('type', 'callable', true);
            $resolvedStatementName = sprintf('callable.%s', $key);

            $subConfig = new ArgumentBag();

            $subConfig
                ->add('type', 'callable')
                ->add('data_type', $config['type'])
                ->add('name', $config['name'])
                ->add('resolved_statement_name', $resolvedStatementName);

            $callableConfig->add('callable.'.$key, $subConfig);

            $callableConfig->setName($resolvedStatementName);

            $this->builtStatements[$resolvedStatementName] = $callableConfig;
        }
    }
    /**
     * @void
     */
    private function markCompiled()
    {
        $this->isCompiled = true;
    }
    /**
     * @param string $configSource
     * @param string $file
     * @return string
     * @throws ConfigurationException
     */
    private function validateImport(string $configSource, string $file) : string
    {
        $absolutePath = realpath(sprintf(dirname($configSource).'/%s', $file));

        if ($absolutePath === false) {
            throw new ConfigurationException(
                sprintf(
                    'Invalid import file. File %s does not exist or is not readable',
                    $absolutePath
                )
            );
        }

        if (!is_readable($absolutePath)) {
            throw new ConfigurationException(
                sprintf(
                    'Invalid import file. File %s does not exist or is not readable',
                    $absolutePath
                )
            );
        }

        return $absolutePath;
    }
}