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
use BlueDot\Entity\EntityInterface;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Connection\ConnectionFactory;
use BlueDot\Kernel\Kernel;
use Symfony\Component\Yaml\Yaml;
use Test\FakerTrait;

class ResultTest extends BaseTest
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

    public function test_simple_statement_result()
    {
        $configSource = __DIR__.'/../config/result/prepared_execution_test.yml';

        $blueDot = new BlueDot($configSource);

        $promise = $blueDot->execute('simple.select.find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);

        /** @var EntityInterface|Entity $entity */
        $entity = $promise->getEntity();

        $data = $entity->getData();

        static::assertInternalType('array', $data);
        static::assertInternalType('array', $promise->getArrayResult());
        static::assertInternalType('array', $entity->toArray());

        static::assertEquals($entity->getType(), 'simple');
        static::assertArrayHasKey('row_count', $entity->toArray());
        static::assertArrayHasKey('data', $entity->toArray());
        static::assertArrayNotHasKey('row_count', $data);
        static::assertArrayNotHasKey('data', $data);
    }

    public function test_promise_functionality()
    {
        $configSource = __DIR__.'/../config/result/prepared_execution_test.yml';

        $blueDot = new BlueDot($configSource);

        $promise = $blueDot->execute('simple.select.find_all_users');

        static::assertInstanceOf(PromiseInterface::class, $promise);

        $promise = $blueDot->execute('simple.delete.delete_user_by_id', [
            'id' => 3,
        ]);

        $callbackCalled = false;
        $isSuccess = false;
        $promise->onResultReady(function(EntityInterface $entity) use (&$callbackCalled, &$isSuccess) {
            $callbackCalled = true;

            if ($entity->getRowCount() === 1) {
                $isSuccess = true;
            }
        });

        static::assertTrue($callbackCalled);
        static::assertTrue($isSuccess);

        static::assertInstanceOf(PromiseInterface::class, $promise);
    }

    public function test_promise_failure()
    {
        $configSource = __DIR__.'/../config/result/prepared_execution_test.yml';

        $blueDot = new BlueDot($configSource);

        $promise = $blueDot->execute('simple.delete.delete_user_by_id', [
            'id' => 123,
        ]);

        $callbackCalled = false;
        $isSuccess = false;
        $promise->onResultReady(function(EntityInterface $entity) use (&$callbackCalled, &$isSuccess) {
            $callbackCalled = true;

            if ($entity->getRowCount() !== 0) {
                $isSuccess = true;
            }
        });

        static::assertTrue($callbackCalled);
        static::assertFalse($isSuccess);
    }

    public function test_scenario_result_success()
    {

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