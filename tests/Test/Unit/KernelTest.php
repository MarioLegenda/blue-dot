<?php

namespace Test\Unit;

use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Compiler;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Kernel\Connection\ConnectionFactory;
use BlueDot\Kernel\Kernel;
use BlueDot\Kernel\Strategy\ScenarioStrategy;
use BlueDot\Kernel\Strategy\SimpleStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Test\Model\User;

class KernelTest extends TestCase
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

    public function test_simple_execution_context()
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

        static::assertTrue($compiler->isCompiled());

        $statementName = 'simple.select.find_by_id';

        /** @var SimpleConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        $kernel = new Kernel($compiledConfiguration, [
            'id' => 1,
        ]);

        $kernel->validateKernel();
    }

    public function test_simple_execution_invalid_context()
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

        static::assertTrue($compiler->isCompiled());

        $statementName = 'simple.select.invalid_statement_sql';

        /** @var SimpleConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        $kernel = new Kernel($compiledConfiguration);

        $entersInvalidStatementException = false;
        try {
            $kernel->validateKernel();
        } catch (\RuntimeException $e) {
            $entersInvalidStatementException = true;
        }

        static::assertTrue($entersInvalidStatementException);
    }

    public function test_simple_parameters()
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

        static::assertTrue($compiler->isCompiled());

        $statementName = 'simple.select.find_all';

        /** @var SimpleConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        $kernel = new Kernel($compiledConfiguration);

        $kernel->validateKernel();
    }

    public function test_full_scenario()
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

        $invalidParametersExceptionThrown = false;
        try {
            $kernel = new Kernel($compiledConfiguration, [
                'id' => 1,
            ]);

            $kernel->validateKernel();
        } catch (\RuntimeException $e) {
            echo sprintf("\n%s\n", $e->getMessage());

            $invalidParametersExceptionThrown = true;
        }

        static::assertTrue($invalidParametersExceptionThrown);

        $invalidParametersExceptionThrown = false;
        try {
            $kernel = new Kernel($compiledConfiguration, [
                'first_statement' => [
                    'id' => 1,
                ],
            ]);

            $kernel->validateKernel();
        } catch (\RuntimeException $e) {
            echo sprintf("\n%s\n", $e->getMessage());

            $invalidParametersExceptionThrown = true;
        }

        static::assertTrue($invalidParametersExceptionThrown);

        $kernel = new Kernel($compiledConfiguration, [
            'first_statement' => [
                'id' => 1,
            ],
            'insert_statement' => [
                'id' => 1,
                'other' => 'other',
            ],
        ]);

        $kernel->validateKernel();
    }

    public function test_kernel_strategy_creation()
    {
        $file = $this->simpleConfig['file'];
        $configArray = $this->simpleConfig['config'];

        $connection = ConnectionFactory::createConnection([
            'host' => 'dummy_host',
            'database_name' => 'dummy_database_name',
            'user' => 'dummy_user',
            'password' => 'dummy_password',
        ]);

        $compiler = new Compiler(
            $file,
            $configArray['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($configArray),
            new ImportCollection()
        );

        static::assertTrue($compiler->isCompiled());

        $statementName = 'simple.select.find_all';

        /** @var SimpleConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        $kernel = new Kernel($compiledConfiguration);

        $kernel->validateKernel();
        $strategy = $kernel->createStrategy($connection);

        static::assertInstanceOf(SimpleStrategy::class, $strategy);

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

        static::assertTrue($compiler->isCompiled());

        $statementName = 'scenario.full_scenario';

        /** @var ScenarioConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        $kernel = new Kernel($compiledConfiguration, [
            'first_statement' => [
                'id' => 1,
            ],
            'insert_statement' => [
                'id' => 1,
                'other' => 'other',
            ],
        ]);

        $kernel->validateKernel();
        $strategy = $kernel->createStrategy($connection);

        static::assertInstanceOf(ScenarioStrategy::class, $strategy);
    }
}
