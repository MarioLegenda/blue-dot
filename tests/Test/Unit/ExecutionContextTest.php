<?php

namespace Test\Unit;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Compiler;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Database\Execution\ExecutionContext;
use BlueDot\Exception\BlueDotRuntimeException;
use Symfony\Component\Yaml\Yaml;

class ExecutionContextTest extends \PHPUnit_Framework_TestCase
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

    public function test_simple_statement_parameters()
    {
        $parsedConfiguration = $this->simpleConfig['config'];

        $compiler = new Compiler(
            $this->simpleConfig['file'],
            $parsedConfiguration['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($parsedConfiguration),
            new ImportCollection()
        );

        $statementName = 'simple.select.find_by_id';
        $statement = $compiler->compile($statementName);

/*        $this->assertInvalidParameters($statement, [
            'i' => 5
        ]);
        $this->assertInvalidParameters($statement, []);
        $this->assertInvalidParameters($statement, [5]);

        $this->assertValidParameters($statement, [
            'id' => 5,
        ]);*/
    }

    public function test_scenario_statement_parameters()
    {
        $parsedConfiguration = $this->scenarioConfig['config'];

        $compiler = new Compiler(
            $this->scenarioConfig['file'],
            $parsedConfiguration['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($parsedConfiguration),
            new ImportCollection()
        );

        $statement = $compiler->compile('scenario.only_selects.select_first_language');

/*        $this->assertInvalidParameters($statement, []);
        $this->assertInvalidParameters($statement, [
            'select_first_language' => [],
        ]);
        $this->assertInvalidParameters($statement, [
            'select_first_language' => [
                'id' => 4,
            ],
        ]);*/
        $this->assertInvalidParameters($statement, [
            'select_first_language' => [
                'id' => 5
            ],
            'select_second_language' => [
                'id' => 5
            ],
        ]);
        $this->assertInvalidParameters($statement, [
            'select_first_language' => [5],
            'select_second_language' => [
                'id' => 5,
            ],
        ]);
        $this->assertInvalidParameters($statement, [
            'select_first_language' => [5],
            'select_second_language' => [7],
        ]);
    }
    /**
     * @param ArgumentBag $statement
     * @param array $parameters
     */
    private function assertInvalidParameters(ArgumentBag $statement, array $parameters = [])
    {
        $enteredParametersException = false;
        try {
            $executionContext = new ExecutionContext($statement, $parameters);

            $executionContext->runTasks();
        } catch (BlueDotRuntimeException $e) {
            var_dump($e->getMessage());
            $enteredParametersException = true;
        }

        static::assertTrue($enteredParametersException);
    }
    /**
     * @param ArgumentBag $statement
     * @param array $parameters
     * @throws BlueDotRuntimeException
     */
    private function assertValidParameters(ArgumentBag $statement, array $parameters)
    {
        $executionContext = new ExecutionContext($statement, $parameters);

        $executionContext->runTasks();
    }
}