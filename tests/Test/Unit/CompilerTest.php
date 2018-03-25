<?php

namespace Test\Unit;

use BlueDot\Common\ArgumentBag;
use BlueDot\Configuration\Compiler;
use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Entity\Model;
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
            'config' => Yaml::parse(file_get_contents($simpleConfig))
        ];

        $this->scenarioConfig = [
            'file' => $scenarioConfig,
            'config' => Yaml::parse(file_get_contents($scenarioConfig))
        ];

        $this->callableConfig = [
            'file' => $callableConfig,
            'config' => Yaml::parse(file_get_contents($callableConfig)),
        ];
    }

/*    public function test_simple_statements_compiler()
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

        static::assertInstanceOf(ArgumentBag::class, $statement);

        static::assertEquals('simple', $statement->get('type'));
        static::assertEquals('select', $statement->get('statement_type'));
        static::assertEquals('find_by_id', $statement->get('statement_name'));
        static::assertEquals($statementName, $statement->get('resolved_statement_name'));

        static::assertTrue($statement->has('config_parameters'));
        static::assertNotEmpty($statement->get('config_parameters'));

        static::assertTrue($statement->has('model'));
        static::assertInstanceOf(Model::class, $statement->get('model'));

        static::assertTrue($compiler->isCompiled());
    }*/

    public function test_scenario_statement_compiler()
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

        $statement = $compiler->compile('scenario.only_selects');

        static::assertInstanceOf(ArgumentBag::class, $statement);

        static::assertEquals('only_selects', $statement->get('root_config')->get('scenario_name'));
        static::assertTrue($statement->get('root_config')->get('atomic'));

        $statement = $compiler->compile('scenario.only_selects');

        static::assertInstanceOf(ArgumentBag::class, $statement);

        static::assertEquals('only_selects', $statement->get('root_config')->get('scenario_name'));
        static::assertTrue($statement->get('root_config')->get('atomic'));
    }

/*    public function test_callable_statement_compiler()
    {
        $parsedConfiguration = $this->callableConfig['config'];

        $compiler = new Compiler(
            $this->scenarioConfig['file'],
            $parsedConfiguration['configuration'],
            new ArgumentValidator(),
            new StatementValidator(),
            new ConfigurationValidator($parsedConfiguration),
            new ImportCollection()
        );

        $statementName = 'callable.callable_service';
        $statement = $compiler->compile($statementName);

        static::assertEquals($statementName, $statement->getName());

        static::assertEquals('callable', $statement->get('type'));
    }*/
}