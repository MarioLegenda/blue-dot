<?php

namespace Test\Unit;

use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Connection\ConnectionFactory;
use BlueDot\Kernel\Kernel;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Result\InsertQueryResult;
use BlueDot\Result\NullQueryResult;
use BlueDot\Result\SelectQueryResult;
use BlueDot\Result\UpdateQueryResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Compiler;

use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use Test\FakerTrait;

class KernelResultTest extends TestCase
{
    use FakerTrait;
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * @var array $simpleConfig
     */
    private $simpleConfig;
    /**
     * @var array $scenarioConfig
     */
    private $scenarioConfig;

    public function setUp()
    {
        $this->connection = ConnectionFactory::createConnection([
            'host' => '127.0.0.1',
            'database_name' => 'blue_dot',
            'user' => 'root',
            'password' => 'root'
        ]);

        $simpleConfig = __DIR__ . '/../config/result/simple_statement_test.yml';
        $scenarioConfig = __DIR__ . '/../config/result/scenario_statement_test.yml';

        $this->simpleConfig = [
            'file' => $simpleConfig,
            'config' => Yaml::parse($simpleConfig)
        ];

        $this->scenarioConfig = [
            'file' => $scenarioConfig,
            'config' => Yaml::parse($scenarioConfig)
        ];

        $this->setUpUsers();

        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->connection->getPDO()->exec('TRUNCATE TABLE user');
    }

    public function test_simple_statement_insert()
    {
        $statementName = 'simple.insert.insert_user';

        $kernel = $this->prepareSimpleStatementKernel(
            $statementName,
            [
                'username' => $this->getFaker()->email,
                'lastname' => $this->getFaker()->lastName,
                'name' => $this->getFaker()->name,
            ]
        );

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(SimpleConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('last_insert_id', $result);

        static::assertEmpty($result['data']);
        static::assertInternalType('int', (int) $result['last_insert_id']);
    }

    public function test_simple_statement_select()
    {
        $statementName = 'simple.select.find_all_users';

        $kernel = $this->prepareSimpleStatementKernel($statementName);

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(SimpleConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertArrayHasKey('data', $result);

        static::assertNotEmpty($result['data']);
        static::assertInternalType('array', $result['data']);

        static::assertArrayHasKey('row_count', $result);
        static::assertInternalType('int', $result['row_count']);

        static::assertEquals(count($result['data']), $result['row_count']);
    }

    public function test_simple_statement_select_with_parameters()
    {
        $statementName = 'simple.select.find_user_by_id';

        $kernel = $this->prepareSimpleStatementKernel(
            $statementName,
            [
                'id' => 1,
            ]
        );

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(SimpleConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertArrayHasKey('data', $result);

        static::assertNotEmpty($result['data']);
        static::assertInternalType('array', $result['data']);

        static::assertArrayHasKey('row_count', $result);
        static::assertInternalType('int', $result['row_count']);

        static::assertEquals(count($result['data']), $result['row_count']);
    }

    public function test_simple_statement_update()
    {
        $statementName = 'simple.update.update_all_users';

        $kernel = $this->prepareSimpleStatementKernel($statementName, [
            'username' => $this->getFaker()->name,
        ]);

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(SimpleConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertArrayHasKey('data', $result);

        static::assertEmpty($result['data']);
        static::assertInternalType('array', $result['data']);

        static::assertArrayHasKey('row_count', $result);
        static::assertInternalType('int', $result['row_count']);

        static::assertGreaterThan(1, $result['row_count']);

        $statementName = 'simple.update.update_user_by_id';

        $kernel = $this->prepareSimpleStatementKernel($statementName, [
            'username' => $this->getFaker()->email,
            'id' => 1,
        ]);

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(SimpleConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertArrayHasKey('data', $result);

        static::assertEmpty($result['data']);
        static::assertInternalType('array', $result['data']);

        static::assertArrayHasKey('row_count', $result);
        static::assertInternalType('int', $result['row_count']);

        static::assertEquals(1, $result['row_count']);
    }

    public function test_simple_statement_delete()
    {
        $statementName = 'simple.delete.delete_all_users';

        $kernel = $this->prepareSimpleStatementKernel($statementName);

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(SimpleConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertArrayHasKey('data', $result);

        static::assertEmpty($result['data']);
        static::assertInternalType('array', $result['data']);

        static::assertArrayHasKey('row_count', $result);
        static::assertInternalType('int', $result['row_count']);

        static::assertGreaterThan(1, $result['row_count']);

        $this->connection->getPDO()->exec('TRUNCATE TABLE user');
        $this->setUpUsers();

        $statementName = 'simple.delete.delete_user_by_id';

        $kernel = $this->prepareSimpleStatementKernel($statementName, [
            'id' => 1,
        ]);

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(SimpleConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertArrayHasKey('data', $result);

        static::assertEmpty($result['data']);
        static::assertInternalType('array', $result['data']);

        static::assertArrayHasKey('row_count', $result);
        static::assertInternalType('int', $result['row_count']);

        static::assertEquals(1, $result['row_count']);
    }

    public function test_scenario_1()
    {
        $statementName = 'scenario.insert_user';
        $kernel = $this->prepareScenarioStatementKernel(
            $statementName,
            [
                'insert_user' => [
                    'username' => $this->getFaker()->email,
                    'name' => $this->getFaker()->name,
                    'lastname' => $this->getFaker()->lastName,
                ],
                'insert_address' => [
                    'address' => $this->getFaker()->address,
                ],
            ]
        );

        $kernel->validateKernel();

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(ScenarioConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertEquals(2, count($result));

        static::assertArrayHasKey('scenario.insert_user.insert_user', $result);
        static::assertArrayHasKey('scenario.insert_user.insert_address', $result);

        static::assertInstanceOf(InsertQueryResult::class, $result['scenario.insert_user.insert_user']);
        static::assertInstanceOf(InsertQueryResult::class, $result['scenario.insert_user.insert_address']);
    }

    public function test_scenario_2()
    {
        $statementName = 'scenario.update_user';

        $kernel = $this->prepareScenarioStatementKernel(
            $statementName,
            [
                'find_user_by_id' => [
                    'user_id' => 1,
                ],
                'update_user' => [
                    'user_id' => 1,
                    'username' => $this->getFaker()->name,
                ],
            ]
        );

        $kernel->validateKernel();

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(ScenarioConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertEquals(2, count($result));

        static::assertArrayHasKey('scenario.update_user.find_user_by_id', $result);
        static::assertArrayHasKey('scenario.update_user.update_user', $result);

        static::assertInstanceOf(SelectQueryResult::class, $result['scenario.update_user.find_user_by_id']);
        static::assertInstanceOf(UpdateQueryResult::class, $result['scenario.update_user.update_user']);
    }

    public function test_scenario_3()
    {
        $statementName = 'scenario.update_user';

        $kernel = $this->prepareScenarioStatementKernel(
            $statementName,
            [
                'find_user_by_id' => [
                    'user_id' => 4353656,
                ],
                'update_user' => [
                    'user_id' => 1,
                    'username' => $this->getFaker()->name,
                ],
            ]
        );

        $kernel->validateKernel();

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(ScenarioConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertEquals(1, count($result));

        static::assertArrayHasKey('scenario.update_user.find_user_by_id', $result);
        static::assertArrayNotHasKey('scenario.update_user.update_user', $result);

        static::assertInstanceOf(NullQueryResult::class, $result['scenario.update_user.find_user_by_id']);
    }

    public function test_scenario_4()
    {
        $statementName = 'scenario.conditional_insert_user';
        $kernel = $this->prepareScenarioStatementKernel(
            $statementName,
            [
                'find_user_by_id' => [
                    'user_id' => 234524535,
                ],
                'insert_user' => [
                    'username' => $this->getFaker()->email,
                    'name' => $this->getFaker()->name,
                    'lastname' => $this->getFaker()->lastName,
                ],
            ]
        );

        $kernel->validateKernel();

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(ScenarioConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertEquals(2, count($result));

        static::assertArrayHasKey('scenario.conditional_insert_user.find_user_by_id', $result);
        static::assertArrayHasKey('scenario.conditional_insert_user.insert_user', $result);

        static::assertInstanceOf(NullQueryResult::class, $result['scenario.conditional_insert_user.find_user_by_id']);
        static::assertInstanceOf(InsertQueryResult::class, $result['scenario.conditional_insert_user.insert_user']);
    }

    public function test_scenario_5()
    {
        $statementName = 'scenario.use_existing_user';
        $kernel = $this->prepareScenarioStatementKernel(
            $statementName,
            [
                'find_user_by_id' => [
                    'user_id' => 1,
                ],
                'insert_user' => [
                    'username' => $this->getFaker()->email,
                    'name' => $this->getFaker()->name,
                    'lastname' => $this->getFaker()->lastName,
                ],
            ]
        );

        $kernel->validateKernel();

        $strategy = $kernel->createStrategy($this->connection);

        /** @var KernelResultInterface $kernelResult */
        $kernelResult = $kernel->executeStrategy($strategy);

        static::assertInstanceOf(KernelResultInterface::class, $kernelResult);
        static::assertInstanceOf(ScenarioConfiguration::class, $kernelResult->getConfiguration());

        $result = $kernelResult->getResult();

        static::assertNotEmpty($result);
        static::assertInternalType('array', $result);

        static::assertEquals(2, count($result));

        static::assertArrayHasKey('scenario.use_existing_user.find_user_by_id', $result);
        static::assertArrayHasKey('scenario.use_existing_user.insert_user', $result);

        static::assertInstanceOf(SelectQueryResult::class, $result['scenario.use_existing_user.find_user_by_id']);
        static::assertInstanceOf(InsertQueryResult::class, $result['scenario.use_existing_user.insert_user']);

    }
    /**
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     * @throws \BlueDot\Exception\CompileException
     * @throws \BlueDot\Exception\ConfigurationException
     */
    private function setUpUsers()
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

        $statementName = 'simple.insert.insert_user';

        for ($i = 0; $i < 10; $i++) {
            /** @var SimpleConfiguration $compiledConfiguration */
            $compiledConfiguration = $compiler->compile($statementName);

            $kernel = new Kernel($compiledConfiguration, [
                'username' => $this->getFaker()->email,
                'lastname' => $this->getFaker()->lastName,
                'name' => $this->getFaker()->name,
            ]);

            $kernel->validateKernel();

            $strategy = $kernel->createStrategy($this->connection);

            $kernel->executeStrategy($strategy);
        }
    }
    /**
     * @param string $statementName
     * @param array|null $parameters
     * @return Kernel
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     * @throws \BlueDot\Exception\CompileException
     * @throws \BlueDot\Exception\ConfigurationException
     */
    private function prepareSimpleStatementKernel(
        string $statementName,
        array $parameters = null
    ): Kernel {
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

        /** @var SimpleConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        $kernel = new Kernel($compiledConfiguration, $parameters);

        $kernel->validateKernel();

        return $kernel;
    }
    /**
     * @param string $statementName
     * @param array|null $parameters
     * @return Kernel
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     * @throws \BlueDot\Exception\CompileException
     * @throws \BlueDot\Exception\ConfigurationException
     */
    private function prepareScenarioStatementKernel(
        string $statementName,
        array $parameters = null
    ): Kernel {
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

        /** @var SimpleConfiguration $compiledConfiguration */
        $compiledConfiguration = $compiler->compile($statementName);

        $kernel = new Kernel($compiledConfiguration, $parameters);

        $kernel->validateKernel();

        return $kernel;
    }
}