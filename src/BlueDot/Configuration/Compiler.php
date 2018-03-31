<?php

namespace BlueDot\Configuration;

use BlueDot\Configuration\Flow\ScenarioFlow;
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
     * @return null
     * @throws ConfigurationException
     */
    private function compileScenarioStatement()
    {
        if (!array_key_exists('scenario', $this->configuration)) {
            return null;
        }

        $scenarioConfiguration = Util::instance()->createGenerator($this->configuration['scenario']);

        $scenarioFlow = new ScenarioFlow();

        foreach ($scenarioConfiguration as $scenarioConfigs) {
            $scenarioName = $scenarioConfigs['key'];
            $resolvedScenarioName = sprintf('scenario.%s', $scenarioName);
            $returnData = null;

            $configuration = $scenarioFlow->create(
                $scenarioName,
                $scenarioConfigs['item'],
                $this->imports
            );

            $this->builtStatements[$resolvedScenarioName] = $configuration;
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