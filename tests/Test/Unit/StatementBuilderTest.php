<?php

namespace Test\Unit;

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
use BlueDot\StatementBuilder\StatementBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Test\FakerTrait;

class StatementBuilderTest extends BaseTest
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
        parent::setUp();

        $this->connection = ConnectionFactory::createConnection([
            'host' => '127.0.0.1',
            'database_name' => 'blue_dot',
            'user' => 'root',
            'password' => 'root'
        ]);

        $simpleConfig = __DIR__ . '/../config/result/simple_statement_test.yml';
        $scenarioConfig = __DIR__ . '/../config/result/scenario_statement_test.yml';

        $method = (method_exists(Yaml::class, 'parseFile')) ? 'parseFile' : 'parse';

        $this->simpleConfig = [
            'file' => $simpleConfig,
            'config' => Yaml::{$method}($simpleConfig)
        ];

        $this->scenarioConfig = [
            'file' => $scenarioConfig,
            'config' => Yaml::{$method}($scenarioConfig)
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

    public function test_statement_builder()
    {
        $statementBuilder = new StatementBuilder($this->connection);

        /** @var PromiseInterface $result */
        $result = $statementBuilder
            ->addSql('SELECT * FROM user WHERE id = :id')
            ->addParameter('id', 1)
            ->execute();

        static::assertInstanceOf(PromiseInterface::class, $result);

        $entity = $result->getOriginalEntity();

        static::assertInstanceOf(Entity::class, $entity);

        $arrayResult = $entity->toArray();

        static::assertNotEmpty($arrayResult);
        static::assertInternalType('array', $arrayResult);

        static::assertArrayHasKey('sql_type', $arrayResult);
        static::assertEquals('select', $arrayResult['sql_type']);

        static::assertArrayHasKey('row_count', $arrayResult);
        static::assertGreaterThan(0, $arrayResult['row_count']);

        static::assertArrayHasKey('data', $arrayResult);
        static::assertNotEmpty($arrayResult['data']);
        static::assertInternalType('array', $arrayResult['data']);
    }
    /**
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
}