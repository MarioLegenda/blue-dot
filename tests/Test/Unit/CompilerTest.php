<?php

namespace Test\Unit;

use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\FlowProductInterface;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Compiler;
use BlueDot\Configuration\Flow\Scenario\ForeignKey;
use BlueDot\Configuration\Flow\Scenario\RootConfiguration;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Scenario\UseOption;
use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Configuration\Flow\Simple\Model;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use BlueDot\Configuration\Flow\Simple\WorkConfig;

use BlueDot\Configuration\Flow\Scenario\Metadata as ScenarioMetadata;
use BlueDot\Configuration\Flow\Simple\Metadata as SimpleMetadata;

class CompilerTest extends TestCase
{
    /**
     * @var array $simpleConfig
     */
    private $simpleConfig;
    /**
     * @var array $scenarioConfig
     */
    private $scenarioConfig;
    /**
     * @var array $serviceConfig
     */
    private $serviceConfig;

    public function setUp()
    {
        $simpleConfig = __DIR__.'/../config/simple_statement_test.yml';
        $scenarioConfig = __DIR__.'/../config/scenario_statement_test.yml';
        $serviceConfig = __DIR__.'/../config/service_statement_test.yml';

        $this->simpleConfig = [
            'file' => $simpleConfig,
            'config' => Yaml::parse($simpleConfig)
        ];

        $this->scenarioConfig = [
            'file' => $scenarioConfig,
            'config' => Yaml::parse($scenarioConfig)
        ];

        $this->serviceConfig = [
            'file' => $serviceConfig,
            'config' => Yaml::parse($serviceConfig),
        ];
    }

    public function test_callable_compiler()
    {
        $file = $this->serviceConfig['file'];
        $configArray = $this->serviceConfig['config'];

        $compiler = new Compiler(
            $file,
            $configArray['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($configArray),
            new ImportCollection()
        );

        $statementName = 'service.service_one';

        /** @var ServiceConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        static::assertInstanceOf(ServiceConfiguration::class, $compiledConfiguration);

        static::assertEquals($compiledConfiguration->getResolvedServiceName(), $statementName);

        static::assertNotEmpty($compiledConfiguration->getClass());
        static::assertInternalType('string', $compiledConfiguration->getClass());
    }

    public function test_simple_no_parameters_compiler()
    {
        $file = $this->simpleConfig['file'];
        $configArray = $this->simpleConfig['config'];

        $compiler = new Compiler(
            $file,
            $configArray['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($configArray),
            new ImportCollection()
        );

        $statementName = 'simple.select.find_all';

        static::assertTrue($compiler->isCompiled());

        /** @var SimpleConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        static::assertInstanceOf(FlowProductInterface::class, $compiledConfiguration);
        static::assertEquals($statementName, $compiledConfiguration->getName());

        static::assertInstanceOf(SimpleMetadata::class, $compiledConfiguration->getMetadata());
        static::assertInstanceOf(WorkConfig::class, $compiledConfiguration->getWorkConfig());

        /** @var SimpleMetadata $metadata */
        $metadata = $compiledConfiguration->getMetadata();

        static::assertEquals('simple', $metadata->getStatementType());
        static::assertEquals('select', $metadata->getSqlType());
        static::assertEquals('find_all', $metadata->getStatementName());
        static::assertEquals($statementName, $metadata->getResolvedStatementName());
        static::assertEquals('simple.select', $metadata->getResolvedStatementType());

        $workConfig = $compiledConfiguration->getWorkConfig();

        static::assertInternalType('string', $workConfig->getSql());
        static::assertNull($workConfig->getModel());
        static::assertInternalType('array', $workConfig->getConfigParameters());
        static::assertEmpty($workConfig->getConfigParameters());
    }

    public function test_simple_parameterized_compiler()
    {
        $file = $this->simpleConfig['file'];
        $configArray = $this->simpleConfig['config'];

        $compiler = new Compiler(
            $file,
            $configArray['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($configArray),
            new ImportCollection()
        );

        $statementName = 'simple.select.find_by_id';

        static::assertTrue($compiler->isCompiled());

        /** @var SimpleConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        static::assertInstanceOf(FlowProductInterface::class, $compiledConfiguration);
        static::assertEquals($statementName, $compiledConfiguration->getName());

        static::assertInstanceOf(SimpleMetadata::class, $compiledConfiguration->getMetadata());
        static::assertInstanceOf(WorkConfig::class, $compiledConfiguration->getWorkConfig());

        /** @var \BlueDot\Configuration\Flow\Simple\Metadata $metadata */
        $metadata = $compiledConfiguration->getMetadata();

        static::assertEquals('simple', $metadata->getStatementType());
        static::assertEquals('select', $metadata->getSqlType());
        static::assertEquals('find_by_id', $metadata->getStatementName());
        static::assertEquals('simple.select', $metadata->getResolvedStatementType());
        static::assertEquals($statementName, $metadata->getResolvedStatementName());

        $workConfig = $compiledConfiguration->getWorkConfig();

        static::assertInternalType('string', $workConfig->getSql());
        static::assertInternalType('array', $workConfig->getConfigParameters());
        static::assertNotEmpty($workConfig->getConfigParameters());

        static::assertInstanceOf(Model::class, $workConfig->getModel());

        $model = $workConfig->getModel();

        static::assertInternalType('string', $model->getClass());
        static::assertNotEmpty($model->getClass());

        static::assertEmpty($model->getProperties());
    }

    public function test_basic_scenario_compiler()
    {
        $file = $this->scenarioConfig['file'];
        $configArray = $this->scenarioConfig['config'];

        $compiler = new Compiler(
            $file,
            $configArray['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($configArray),
            new ImportCollection()
        );

        $scenarioName = 'scenario.only_selects';

        /** @var ScenarioConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($scenarioName);

        static::assertInstanceOf(ScenarioConfiguration::class, $compiledConfiguration);

        $rootConfiguration = $compiledConfiguration->getRootConfiguration();
        $metadata = $compiledConfiguration->getMetadata();

        static::assertInstanceOf(RootConfiguration::class, $rootConfiguration);
        static::assertNotEmpty($metadata);
        static::assertInternalType('array', $metadata);

        static::assertEquals($scenarioName, $rootConfiguration->getScenarioName());
        static::assertTrue($rootConfiguration->isAtomic());
        static::assertNull($rootConfiguration->getReturnData());

        /** @var ScenarioMetadata $singleMetadata */
        foreach ($metadata as $singleMetadata) {
            static::assertInstanceOf(ScenarioMetadata::class, $singleMetadata);
            static::assertNotEmpty($singleMetadata->getResolvedScenarioStatementName());
            static::assertNotEmpty($singleMetadata->getScenarioName());
            static::assertNotEmpty($singleMetadata->getSingleScenarioName());
            static::assertNotEmpty($singleMetadata->getSql());
            static::assertNotEmpty($singleMetadata->getSqlType());
            static::assertEquals('select', $singleMetadata->getSqlType());
            static::assertInternalType('boolean', $singleMetadata->canBeEmptyResult());

            static::assertNull($singleMetadata->getIfExistsStatementName());
            static::assertNull($singleMetadata->getIfNotExistsStatementName());
            static::assertNull($singleMetadata->getUseOption());

            static::assertEmpty($singleMetadata->getUserParameters());
            static::assertInternalType('array', $singleMetadata->getUserParameters());

            static::assertNull($singleMetadata->getForeignKey());

            static::assertNotEmpty($singleMetadata->getConfigParameters());
            static::assertInternalType('array', $singleMetadata->getConfigParameters());
        }
    }

    public function test_full_scenario_compiler()
    {
        $file = $this->scenarioConfig['file'];
        $configArray = $this->scenarioConfig['config'];

        $compiler = new Compiler(
            $file,
            $configArray['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($configArray),
            new ImportCollection()
        );

        $scenarioName = 'scenario.full_scenario';

        /** @var ScenarioConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($scenarioName);

        static::assertInstanceOf(ScenarioConfiguration::class, $compiledConfiguration);

        $rootConfiguration = $compiledConfiguration->getRootConfiguration();
        $metadata = $compiledConfiguration->getMetadata();

        static::assertInstanceOf(RootConfiguration::class, $rootConfiguration);
        static::assertNotEmpty($metadata);
        static::assertInternalType('array', $metadata);

        static::assertEquals($scenarioName, $rootConfiguration->getScenarioName());
        static::assertTrue($rootConfiguration->isAtomic());
        static::assertNull($rootConfiguration->getReturnData());

        $foreignKeyAssertEntered = false;
        $useOptionAssertEntered = false;
        $entersIfExistsStatement = false;
        $entersIfNotExistsStatement = false;
        /** @var ScenarioMetadata $singleMetadata */
        foreach ($metadata as $singleMetadata) {
            static::assertInstanceOf(ScenarioMetadata::class, $singleMetadata);
            static::assertNotEmpty($singleMetadata->getResolvedScenarioStatementName());
            static::assertNotEmpty($singleMetadata->getScenarioName());
            static::assertNotEmpty($singleMetadata->getSingleScenarioName());
            static::assertNotEmpty($singleMetadata->getSql());
            static::assertNotEmpty($singleMetadata->getSqlType());
            static::assertInternalType('boolean', $singleMetadata->canBeEmptyResult());

            if (is_string($singleMetadata->getIfExistsStatementName())) {
                static::assertInternalType('string', $singleMetadata->getIfExistsStatementName());

                $entersIfExistsStatement = true;
            }

            if (is_string($singleMetadata->getIfNotExistsStatementName())) {
                static::assertInternalType('string', $singleMetadata->getIfNotExistsStatementName());

                $entersIfNotExistsStatement = true;
            }

            static::assertEmpty($singleMetadata->getUserParameters());
            static::assertInternalType('array', $singleMetadata->getUserParameters());

            /** @var ForeignKey $foreignKey */
            $foreignKey = $singleMetadata->getForeignKey();

            if ($foreignKey instanceof ForeignKey) {
                $foreignKeyAssertEntered = true;

                static::assertNotEmpty($foreignKey->getStatementName());
                static::assertInternalType('string', $foreignKey->getStatementName());

                static::assertNotEmpty($foreignKey->getBindTo());
                static::assertInternalType('string', $foreignKey->getBindTo());
            }

            $useOption = $singleMetadata->getUseOption();

            if ($useOption instanceof UseOption) {
                $useOptionAssertEntered = true;

                static::assertNotEmpty($useOption->getStatementName());
                static::assertInternalType('string', $useOption->getStatementName());

                static::assertNotEmpty($useOption->getValues());
                static::assertInternalType('array', $useOption->getValues());
            }
        }

        static::assertTrue($entersIfExistsStatement);
        static::assertTrue($entersIfNotExistsStatement);
        static::assertTrue($useOptionAssertEntered);
        static::assertTrue($foreignKeyAssertEntered);
    }
}