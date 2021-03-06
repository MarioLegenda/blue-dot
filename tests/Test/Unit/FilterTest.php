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
use BlueDot\Entity\EntityCollection;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Connection\ConnectionFactory;
use BlueDot\Kernel\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Test\FakerTrait;

class FilterTest extends BaseTest
{
    use FakerTrait;
    /**
     * @var array $columns
     */
    private $columns = [
        'id',
        'name',
        'lastname',
        'username',
        'gender',
        'address',
    ];
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

    public function test_by_column_filter()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.by_column_filter_find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);


        $result = $promise->getEntity()->toArray();

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];

        static::assertEquals(1, count($data['data']));
        static::assertArrayHasKey('id', $data['data']);
        static::assertGreaterThan(1, count($data['data']['id']));
    }

    public function test_cascading_filter()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.cascading_filter_find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);

        $result = $promise->getEntity()->toArray();

        static::assertArrayHasKey('data', $result);
        static::assertEquals($result['data']['type'], 'simple');
        static::assertGreaterThan(1, $result['data']['row_count']);

        $data = $result['data']['data'];

        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('username', $data);
        static::assertArrayHasKey('lastname', $data);
        static::assertArrayHasKey('name', $data);
    }

    public function test_normalize_joined_result_filter()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.normalize_joined_result_find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);

        /** @var Entity $entity */
        $entity = $promise->getEntity();

        static::assertInstanceOf(Entity::class, $entity);
    }

    public function test_find_filter()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.find_exact_filter_find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);

        /** @var Entity $entity */
        $entity = $promise->getEntity();

        static::assertNotEmpty($entity->toArray()['data']);
    }

    public function test_normalize_if_one_exists_filter()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.normalize_if_one_exists_filter_find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);

        /** @var Entity $entity */
        $entity = $promise->getEntity();

        static::assertInstanceOf(Entity::class, $entity);

        $data = $entity->toArray()['data']['data'];

        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('username', $data);
        static::assertArrayHasKey('lastname', $data);
        static::assertArrayHasKey('name', $data);
    }

    public function test_filter_on_invalid_sql_type()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        $entersInvalidSqlTypeForFilterException = false;
        try {
            $blueDot->execute('simple.update.update_all_users', [
                'username' => $this->getFaker()->name,
            ]);
        } catch (\RuntimeException $e) {
            $entersInvalidSqlTypeForFilterException = true;
        }

        static::assertTrue($entersInvalidSqlTypeForFilterException);
    }

    public function test_cascade_filter_on_scenario()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        $promise = $blueDot->execute('scenario.select_user');

        static::assertInstanceOf(PromiseInterface::class, $promise);

        /** @var EntityCollection $result */
        $result = $promise->getEntity();

        static::assertInstanceOf(EntityCollection::class, $result);

        $scenarioResult = $result->getEntity('select_user')->toArray()['data'];

        static::assertArrayHasKey('row_count', $scenarioResult);

        static::assertNotEmpty($scenarioResult);
        static::assertInternalType('array', $scenarioResult);
        static::assertArrayHasKey('data', $scenarioResult);
        static::assertNotEmpty($scenarioResult['data']);
        static::assertInternalType('array', $scenarioResult['data']);

        $data = $scenarioResult['data'];

        static::assertArrayHasKey('username', $data);
        static::assertArrayHasKey('name', $data);
        static::assertArrayHasKey('lastname', $data);
    }

    public function test_filter_on_invalid_scenario_type()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        $entersInvalidSqlTypeForFilterException = false;
        try {
            $blueDot->execute('scenario.invalid_filter_insert_user', [
                'insert_user' => [
                    'username' => $this->getFaker()->name,
                    'lastname' => $this->getFaker()->lastName,
                    'name' => $this->getFaker()->name,
                ],
            ]);
        } catch (\RuntimeException $e) {
            $entersInvalidSqlTypeForFilterException = true;
        }

        static::assertTrue($entersInvalidSqlTypeForFilterException);
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
    /**
     * @param int $numOfEntries
     * @return array
     */
    private function getArrayResult(int $numOfEntries): array
    {
        $entries = [];
        for ($i = 0; $i < $numOfEntries; $i++) {
            $temp = [];

            foreach ($this->columns as $column) {
                if ($column === 'id') {
                    $temp[$column] = $i;

                    continue;
                }

                if ($column === 'name') {
                    if (($i % 2) === 0) {
                        $temp[$column] = 'name';

                        continue;
                    }
                }

                $temp[$column] = $this->getFaker()->name;
            }

            $entries[] = $temp;
        }

        return ['data' => $entries];
    }
    /**
     * @param int $numOfEntries
     * @return array
     */
    private function getNormalizationArray(int $numOfEntries): array
    {
        $normalization = [];
        for ($i = 0; $i < $numOfEntries; $i++) {
            $normalization = array_merge($normalization, $this->getArrayResult($numOfEntries)['data']);
        }

        return ['data' => $normalization];
    }
}