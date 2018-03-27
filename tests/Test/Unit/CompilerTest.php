<?php

namespace Test\Unit;

use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Compiler;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\Model\ConfigurationInterface;
use BlueDot\Database\Model\MetadataInterface;
use BlueDot\Database\Model\Model;
use BlueDot\Database\Model\Simple\SimpleConfiguration;
use BlueDot\Database\Model\WorkConfig;
use BlueDot\Database\Model\WorkConfigInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

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
     * @var array $callableConfig
     */
    private $callableConfig;

    public function setUp()
    {
        $simpleConfig = __DIR__.'/../config/simple_statement_test.yml';
        $scenarioConfig = __DIR__.'/../config/scenario_statement_test.yml';
        $callableConfig = __DIR__.'/../config/callable_statement_test.yml';

        $this->simpleConfig = [
            'file' => $simpleConfig,
            'config' => Yaml::parse($simpleConfig)
        ];

        $this->scenarioConfig = [
            'file' => $scenarioConfig,
            'config' => Yaml::parse($scenarioConfig)
        ];

        $this->callableConfig = [
            'file' => $callableConfig,
            'config' => Yaml::parse($callableConfig),
        ];
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

        static::assertInstanceOf(ConfigurationInterface::class, $compiledConfiguration);
        static::assertEquals($statementName, $compiledConfiguration->getName());

        static::assertInstanceOf(MetadataInterface::class, $compiledConfiguration->getMetadata());
        static::assertInstanceOf(WorkConfig::class, $compiledConfiguration->getWorkConfig());

        $metadata = $compiledConfiguration->getMetadata();

        static::assertEquals('simple', $metadata->getType());
        static::assertEquals('select', $metadata->getStatementType());
        static::assertEquals('find_all', $metadata->getStatementName());
        static::assertEquals($statementName, $metadata->getResolvedStatementName());
        static::assertEquals('simple.select', $metadata->getResolvedStatementType());

        $workConfig = $compiledConfiguration->getWorkConfig();

        static::assertInternalType('string', $workConfig->getSql());
        static::assertNull($workConfig->getModel());
        static::assertNull($workConfig->getConfigParameters());
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

        static::assertInstanceOf(ConfigurationInterface::class, $compiledConfiguration);
        static::assertEquals($statementName, $compiledConfiguration->getName());

        static::assertInstanceOf(MetadataInterface::class, $compiledConfiguration->getMetadata());
        static::assertInstanceOf(WorkConfigInterface::class, $compiledConfiguration->getWorkConfig());

        $metadata = $compiledConfiguration->getMetadata();

        static::assertEquals('simple', $metadata->getType());
        static::assertEquals('select', $metadata->getStatementType());
        static::assertEquals('find_by_id', $metadata->getStatementName());
        static::assertEquals('simple.select', $metadata->getResolvedStatementType());
        static::assertEquals($statementName, $metadata->getResolvedStatementName());

        $workConfig = $compiledConfiguration->getWorkConfig();

        static::assertInternalType('string', $workConfig->getSql());
        static::assertInternalType('array', $workConfig->getConfigParameters());
        static::assertNotEmpty($workConfig->getConfigParameters());

        static::assertInstanceOf(Model::class, $workConfig->getModel());

        $model = $workConfig->getModel();

        static::assertInternalType('string', $model->getName());
        static::assertNotEmpty($model->getName());

        static::assertEmpty($model->getProperties());
    }
}