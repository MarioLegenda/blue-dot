<?php

namespace Test\Unit;

use BlueDot\BlueDot;
use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Compiler;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Entity\BaseEntity;
use BlueDot\Entity\EntityCollection;
use BlueDot\Entity\EntityInterface;
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

        static::assertTrue(!empty($promise->getEntity()->toArray()['data']));
    }

    public function test_blue_dot_execution()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue(!empty($promise->getEntity()->toArray()['data']));

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

        /** @var EntityCollection $entityCollection */
        $entityCollection = $promise->getEntity();

        static::assertInstanceOf(EntityCollection::class, $entityCollection);

        static::assertNotEmpty($entityCollection->getEntity('insert_user'));
        static::assertNotEmpty($entityCollection->getEntity('insert_address'));
    }

    public function test_composite_foreign_key()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue(!empty($promise->getEntity()->toArray()['data']));

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

        /** @var EntityCollection $entityCollection */
        $entityCollection = $promise->getEntity();

        static::assertInstanceOf(EntityCollection::class, $entityCollection);

        static::assertNotEmpty($entityCollection->getEntity('insert_user'));
        static::assertNotEmpty($entityCollection->getEntity('insert_address'));
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
        static::assertTrue(!empty($promise->getEntity()->toArray()['inserted_ids']));

        /** @var array $data */
        $data = $promise->getEntity()->toArray();

        static::assertArrayHasKey('inserted_ids', $data);
        static::assertNotEmpty($data['inserted_ids']);
        static::assertInternalType('array', $data['inserted_ids']);

        static::assertArrayHasKey('last_insert_id', $data);
        static::assertInternalType('int', $data['last_insert_id']);
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
        static::assertTrue(!empty($promise->getEntity()->toArray()['data']));
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

        /** @var EntityCollection $entityCollection */
        $entityCollection = $promise->getEntity();

        static::assertTrue($entityCollection->hasEntity('insert_user'));
        static::assertTrue($entityCollection->hasEntity('insert_address'));

        /** @var EntityInterface $entity */
        $entity = $entityCollection->getEntity('insert_user');

        static::assertInstanceOf(EntityInterface::class, $entity);
        static::assertEquals($entity->getName(), 'insert_user');

        $data = $entity->toArray();

        static::assertArrayHasKey('last_insert_id', $data);
        static::assertArrayHasKey('row_count', $data);
        static::assertArrayHasKey('type', $data);

        static::assertGreaterThanOrEqual(1, $data['row_count']);
        static::assertGreaterThanOrEqual(1, $data['last_insert_id']);

        static::assertEquals($data['type'], 'scenario');
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

        /** @var EntityCollection $entityCollection */
        $entityCollection = $promise->getEntity();

        static::assertTrue($entityCollection->hasEntity('find_user_by_id'));

        $entity = $entityCollection->getEntity('find_user_by_id');
    }

    public function test_blue_dot_other_type_execution()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('scenario.table_creation');

        /** @var EntityCollection $entityCollection */
        $entityCollection = $promise->getEntity();

        static::assertInstanceOf(EntityCollection::class, $entityCollection);

        static::assertTrue($entityCollection->hasEntity('create_database'));
        static::assertTrue($entityCollection->hasEntity('use_database'));
        static::assertTrue($entityCollection->hasEntity('create_user_table'));
        static::assertTrue($entityCollection->hasEntity('create_address_table'));
        static::assertTrue($entityCollection->hasEntity('create_normalized_user'));
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