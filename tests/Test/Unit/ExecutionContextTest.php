<?php

namespace Test\Unit;

use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Compiler;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\Execution\ExecutionContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class ExecutionContextTest extends TestCase
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

        $compiledConfiguration = $compiler->compile($statementName);

        $executionContext = new ExecutionContext($compiledConfiguration, [
            'id' => 1,
        ]);

        $executionContext->runTasks();
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

        $compiledConfiguration = $compiler->compile($statementName);

        $executionContext = new ExecutionContext($compiledConfiguration);

        $entersInvalidStatementException = false;
        try {
            $executionContext->runTasks();
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

        $compiledConfiguration = $compiler->compile($statementName);

        $executionContext = new ExecutionContext($compiledConfiguration);

        $executionContext->runTasks();
    }
}