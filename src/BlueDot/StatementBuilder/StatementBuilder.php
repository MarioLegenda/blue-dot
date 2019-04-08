<?php

namespace BlueDot\StatementBuilder;

use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Configuration\Flow\SimpleFlow;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Entity\Promise;
use BlueDot\Entity\PromiseInterface;
use BlueDot\Exception\BlueDotRuntimeException;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Kernel;

class StatementBuilder
{
    /**
     * @var string $sql
     */
    private $sql;
    /**
     * @var Connection|null
     */
    private $connection = null;
    /**
     * @var array $configParameters
     */
    private $configParameters = array();
    /**
     * @var array $userParameters
     */
    private $userParameters = array();
    /**
     * StatementBuilder constructor.
     * @param Connection $connection
     *
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    /**
     * @param string $sql
     * @return StatementBuilder
     */
    public function addSql(string $sql) : StatementBuilder
    {
        $this->sql = $sql;

        return $this;
    }
    /**
     * @param string $key
     * @param $value
     * @return StatementBuilder
     * @throws BlueDotRuntimeException
     */
    public function addParameter(string $key, $value) : StatementBuilder
    {
        $this->configParameters[] = $key;

        if (array_key_exists($key, $this->userParameters)) {
            throw new BlueDotRuntimeException(
                sprintf('Invalid statement builder parameters. Parameter with key \'%s\' already exists', $key)
            );
        }

        $this->userParameters[$key] = $value;

        return $this;
    }
    /**
     * @return PromiseInterface
     */
    public function execute() : PromiseInterface
    {
        return $this->makeResult();
    }
    /**
     * @return PromiseInterface
     */
    private function makeResult(): PromiseInterface
    {
        $configuration = $this->createConfiguration();

        $kernel = new Kernel($configuration, $this->userParameters);

        $kernel->validateKernel();

        $strategy = $kernel->createStrategy($this->connection);

        $kernelResult = $kernel->executeStrategy($strategy);

        $result = $kernel->convertKernelResultToUserFriendlyResult($kernelResult);

        return new Promise($result);
    }
    /**
     * @return SimpleConfiguration
     */
    private function createConfiguration(): SimpleConfiguration
    {
        $autoGeneratedStatementName = sprintf(
            'simple.%s.%s',
            $this->determineTypeFromSql($this->sql),
            substr(md5(rand(99999, 999999)), 0, 6)
        );

        $config = [
            'sql' => $this->sql,
            'parameters' => $this->configParameters,
        ];

        $simpleFlow = new SimpleFlow();

        return $simpleFlow->create(
            $autoGeneratedStatementName,
            $config,
            new ImportCollection()
        );
    }
    /**
     * @param string $sql
     * @return string
     */
    private function determineTypeFromSql(string $sql): string
    {
        $typeMatch = preg_match('#^[a-zA-Z]+\s#i', $sql, $matches);

        if ($typeMatch === 0) {
            $message = sprintf(
                'Statement builder could not determine correct sql type from sql \'%s\'',
                $sql
            );

            throw new \RuntimeException($message);
        }

        return trim(strtolower($matches[0]));
    }
}