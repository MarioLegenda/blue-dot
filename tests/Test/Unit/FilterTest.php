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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Test\FakerTrait;

class FilterTest extends TestCase
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
        $this->connection = ConnectionFactory::createConnection([
            'host' => '127.0.0.1',
            'database_name' => 'blue_dot',
            'user' => 'root',
            'password' => 'root'
        ]);

        $preparedExecutionConfig = __DIR__ . '/../config/result/prepared_execution_test.yml';

        $this->preparedExecutionConfig = [
            'file' => $preparedExecutionConfig,
            'config' => Yaml::parse($preparedExecutionConfig)
        ];

        $this->setUpUsers();

        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->connection->getPDO()->exec('TRUNCATE TABLE user');
    }

    public function test_by_column_filter()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.by_column_filter_find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        $entity = $promise->getResult();

        static::assertTrue($entity->has('data'));
        static::assertEquals(1, count($entity->get('data')));
        static::assertArrayHasKey('id', $entity->get('data'));
        static::assertGreaterThan(1, count($entity->get('data')['id']));
    }

    public function test_cascading_filter()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.cascading_filter_find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        $entity = $promise->getResult();

        static::assertInstanceOf(Entity::class, $entity);

        $data = $entity->get('data');

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
        static::assertTrue($promise->isSuccess());

        $entity = $promise->getResult();

        static::assertInstanceOf(Entity::class, $entity);
    }

    public function test_find_filter()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.find_exact_filter_find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        $entity = $promise->getResult();

        static::assertNotEmpty($entity->toArray());
        static::assertEquals(2, count($entity->toArray()));
    }

    public function test_normalize_if_one_exists_filter()
    {
        $blueDot = new BlueDot(__DIR__.'/../config/result/prepared_execution_test.yml');

        /** @var PromiseInterface $promise */
        $promise = $blueDot->execute('simple.select.normalize_if_one_exists_filter_find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);
        static::assertTrue($promise->isSuccess());

        $entity = $promise->getResult();

        static::assertInstanceOf(Entity::class, $entity);

        $data = $entity->get('data');

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
        static::assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        static::assertInstanceOf(Entity::class, $result);

        $scenarioResult = $result->get('select_user');

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