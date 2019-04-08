<?php

namespace Test\Unit;

use BlueDot\BlueDot;
use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Compiler;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Entity\Entity;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Connection\ConnectionFactory;
use BlueDot\Kernel\Kernel;
use Symfony\Component\Yaml\Yaml;
use Test\FakerTrait;

class BlueDotTest extends BaseTest
{
    use FakerTrait;
    /**
     * @var Connection $connection
     */
    private $connection;
    /**
     * @var array $preparedExecutionConfig
     */
    private $preparedExecutionConfig;

    public function setUp()
    {
        parent::setUp();

        $this->connection = ConnectionFactory::createConnection([
            'host' => '127.0.0.1',
            'database_name' => 'blue_dot',
            'user' => 'root',
            'password' => 'root'
        ]);

        $preparedExecutionConfig = __DIR__ . '/../config/result/prepared_execution_test.yml';

        $method = (method_exists(Yaml::class, 'parseFile')) ? 'parseFile' : 'parse';

        $this->preparedExecutionConfig = [
            'file' => $preparedExecutionConfig,
            'config' => Yaml::{$method}($preparedExecutionConfig)
        ];

        $this->setUpUsers();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->connection->getPDO()->exec('TRUNCATE TABLE user');
        $this->connection->getPDO()->exec('TRUNCATE TABLE addresses');
        $this->connection->getPDO()->exec('TRUNCATE TABLE normalized_user');

        $this->connection->close();
    }

    public function test_blue_dot_features()
    {
        $configSource = __DIR__.'/../config/result/prepared_execution_test.yml';

        $blueDot = new BlueDot();

        $blueDot->setConfiguration($configSource);

        $promise = $blueDot->execute('simple.select.find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());
    }

    public function test_blue_dot_execution()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        $promise = $blueDot->execute('scenario.insert_user', [
            'insert_user' => [
                'name' => $this->getFaker()->name,
                'lastname' => $this->getFaker()->lastName,
                'username' => $this->getFaker()->email,
            ],
            'insert_address' => [
                'address' => $this->getFaker()->address,
            ],
        ]);

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());
    }

    public function test_composite_foreign_key()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        $promise = $blueDot->execute('scenario.normalized_user_insert', [
            'insert_user' => [
                'name' => $this->getFaker()->name,
                'lastname' => $this->getFaker()->lastName,
                'username' => $this->getFaker()->email,
            ],
            'insert_address' => [
                'address' => $this->getFaker()->address,
            ],
        ]);

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());
    }

    public function test_multiple_insert_for_simple_statements()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        $parameters = [];

        for ($i = 0; $i < 10; $i++) {
            $parameters[] = [
                'name' => $this->getFaker()->name,
                'lastname' => $this->getFaker()->lastName,
                'username' => $this->getFaker()->userName,
            ];
        }

        $promise = $blueDot->execute('simple.insert.insert_user', $parameters);

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        /** @var Entity $result */
        $entity = $promise->getEntity();

        static::assertTrue($entity->has('inserted_ids'));
        static::assertNotEmpty($entity->get('inserted_ids'));
        static::assertInternalType('array', $entity->get('inserted_ids'));

        static::assertTrue($entity->has('last_insert_id'));
        static::assertInternalType('int', $entity->get('last_insert_id'));
    }

    public function test_multiple_select_for_simple_statements()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        $parameters = [];
        for ($i = 1; $i < 10; $i++) {
            $parameters[] = ['id' => $i];
        }

        $promise = $blueDot->execute('simple.select.find_user_by_id', $parameters);

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());
    }

    public function test_multiple_insert_for_scenario_statements()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        $parameters = [];

        for ($i = 0; $i < 10; $i++) {
            $parameters[] = ['address' => $this->getFaker()->address];
        }

        $promise = $blueDot->execute('scenario.insert_user', [
            'insert_user' => [
                'name' => $this->getFaker()->name,
                'username' => $this->getFaker()->userName,
                'lastname' => $this->getFaker()->lastName,
            ],
            'insert_address' => $parameters,
        ]);

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        /** @var Entity $result */
        $result = $promise->getEntity();

        static::assertTrue($result->has('insert_address'));
        static::assertArrayHasKey('inserted_ids', $result->get('insert_address'));
        static::assertNotEmpty($result->get('insert_address'));
        static::assertInternalType('array', $result->get('insert_address'));

        static::assertArrayHasKey('row_count', $result->get('insert_address'));
        static::assertGreaterThan(1, $result->get('insert_address')['row_count']);

        static::assertArrayHasKey('last_insert_id', $result->get('insert_address'));
        static::assertGreaterThan(1, $result->get('insert_address')['last_insert_id']);
    }

    public function test_multiple_select_for_scenario_statements()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        $parameters = [];

        for ($i = 0; $i < 10; $i++) {
            $parameters[] = ['id' => $i];
        }

        $promise = $blueDot->execute('scenario.select_user_by_id', [
            'find_user_by_id' => $parameters,
        ]);

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        $result = $promise->getEntity()->get('find_user_by_id');

        $data = $result['data'];

        static::assertNotEmpty($data);
        static::assertInternalType('array', $data);

        foreach ($data as $row) {
            static::assertArrayHasKey('row_count', $row);

            $innerData = $row['data'];

            static::assertNotEmpty($innerData);
            static::assertInternalType('array', $innerData);
        }
    }

    public function test_blue_dot_other_type_execution()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('scenario.table_creation');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());
    }
    /**
     * @throws \BlueDot\Exception\ConfigurationException
     */
    private function setUpUsers()
    {
        $file = $this->preparedExecutionConfig['file'];
        $configArray = $this->preparedExecutionConfig['config'];

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
}