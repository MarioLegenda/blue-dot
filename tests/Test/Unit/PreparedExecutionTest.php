<?php

namespace Test\Unit;

use BlueDot\BlueDot;
use BlueDot\BlueDotInterface;
use BlueDot\Common\ArgumentValidator;
use BlueDot\Common\StatementValidator;
use BlueDot\Configuration\Compiler;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Connection\ConnectionFactory;
use BlueDot\Kernel\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Test\FakerTrait;

class PreparedExecutionTest extends TestCase
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
    /**
     * @var BlueDotInterface $blueDot
     */
    private $blueDot;

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

        $this->blueDot = new BlueDot($preparedExecutionConfig);

        $this->setUpUsers();

        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->connection->getPDO()->exec('TRUNCATE TABLE user');
    }

    public function test_prepared_execution()
    {
        $statements = [
            'simple.select.find_all_users' => null,
            'simple.select.find_user_by_id' => [
                'id' => 1,
            ],
            'scenario.insert_user' => [
                'insert_user' => [
                    'username' => $this->getFaker()->userName,
                    'name' => $this->getFaker()->name,
                    'lastname' => $this->getFaker()->lastName,
                ],
                'insert_address' => [
                    'address' => $this->getFaker()->address,
                ],
            ],
        ];

        foreach ($statements as $statement => $parameters) {
            $this->blueDot->prepareExecution($statement, $parameters);
        }

        $promises = $this->blueDot->executePrepared();

        static::assertEquals(count($promises), count($statements));

        /** @var PromiseInterface $promise */
        foreach ($promises as $promise) {
            static::assertInstanceOf(PromiseInterface::class, $promise);
        }
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