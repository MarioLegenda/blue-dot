<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Scenario\RootConfiguration;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Kernel\Result\Scenario\KernelResult;
use BlueDot\Kernel\Result\Scenario\KernelResultCollection;
use BlueDot\Kernel\Strategy\Enum\IfExistsType;
use BlueDot\Kernel\Strategy\Enum\IfNotExistsType;
use BlueDot\Result\NullQueryResult;

class ScenarioStrategy implements StrategyInterface
{
    /**
     * @var ScenarioConfiguration $configuration
     */
    private $configuration;
    /**
     * @var Connection $connection
     */
    protected $connection;
    /**
     * @var array $metadata
     */
    protected $metadata;
    /**
     * @var KernelResultCollection $results
     */
    protected $results;
    /**
     * ScenarioStrategy constructor.
     * @param ScenarioConfiguration $configuration
     * @param Connection $connection
     */
    public function __construct(
        ScenarioConfiguration $configuration,
        Connection $connection
    ) {
        $this->connection = $connection;
        $this->configuration = $configuration;

        $this->results = new KernelResultCollection();
    }
    /**
     * @return KernelResultInterface
     * @throws \RuntimeException
     * @throws \BlueDot\Exception\ConnectionException
     */
    public function execute() : KernelResultInterface
    {
        $this->connection->connect();

        /** @var RootConfiguration $rootConfiguration */
        $rootConfiguration = $this->configuration->getRootConfiguration();

        if ($rootConfiguration->isAtomic() and !$this->connection->getPDO()->inTransaction()) {
            $this->connection->getPDO()->beginTransaction();
        }

        $this->metadata = $this->configuration->getMetadata();

        try {
            /** @var Metadata $item */
            foreach ($this->metadata as $item) {
                if ($item->hasIfExistsStatement() or $item->hasIfNotExistsStatement()) {
                    $existsStatementName = $item->getExistsStatementName();
                    $fullExistsStatementName = $item->createExistsFullStatementName();

                    /** @var TypeInterface $existsType */
                    $existsType = $item->getExistsStatementType();

                    /** @var Metadata $existsStatementMetadata */
                    $existsStatementMetadata = $this->metadata[$existsStatementName];

                    if (!$this->results->has($fullExistsStatementName)) {
                        $recursiveStatementExecution = new RecursiveStatementExecution(
                            $existsStatementMetadata,
                            $this->results,
                            $this->connection
                        );

                        $recursiveStatementExecution->execute($this->metadata);

                        unset($recursiveStatementExecution);
                    }

                    $existsStatementResult = $this->results->get($fullExistsStatementName);

                    if ($existsType->equals(IfExistsType::fromValue())) {
                        if ($existsStatementResult instanceof NullQueryResult) {
                            continue;
                        }
                    }

                    if ($existsType->equals(IfNotExistsType::fromValue())) {
                        if (!$existsStatementResult instanceof NullQueryResult) {
                            continue;
                        }
                    }
                }

                if ($this->results->has($item->getResolvedScenarioStatementName())) {
                    continue;
                }

                $recursiveStatementExecution = new RecursiveStatementExecution(
                    $item,
                    $this->results,
                    $this->connection
                );

                $recursiveStatementExecution->execute($this->metadata);

                unset($recursiveStatementExecution);
            }
        } catch (\PDOException $e) {
            if ($rootConfiguration->isAtomic()) {
                $this->handleRollback($rootConfiguration);
            }

            $message = sprintf(
                'A PDOException has been thrown for statement \'%s\' with message \'%s\'',
                $this->configuration->getRootConfiguration()->getScenarioName(),
                $e->getMessage()
            );

            throw new \RuntimeException($message);
        } catch (\RuntimeException $e) {
            if ($rootConfiguration->isAtomic()) {
                $this->handleRollback($rootConfiguration);
            }

            throw new \RuntimeException($e->getMessage());
        }

        try {
            if ($rootConfiguration->isAtomic()) {
                $this->connection->getPDO()->commit();
            }
        } catch (\Exception $e) {
            $this->handleRollback($rootConfiguration);
        }

        return new KernelResult(
            $this->configuration,
            $this->results->toArray()
        );
    }
    /**
     * @inheritdoc
     * @throws \RuntimeException
     */
    public function getResult(\PDOStatement $pdoStatement = null) : KernelResultInterface
    {
        $class = get_class($this);

        $message = sprintf(
            '%s::getResult() is not implemented in %s',
            StrategyInterface::class,
            $class
        );

        throw new \RuntimeException($message);
    }
    /**
     * @param RootConfiguration $rootConfiguration
     * @throws \RuntimeException
     */
    private function handleRollback(RootConfiguration $rootConfiguration)
    {
        if ($rootConfiguration->isAtomic()) {
            if (!$this->connection->getPDO()->inTransaction()) {
                $message = sprintf(
                    'Internal error. Scenario %s should be in transaction to rollback but it isn\'t',
                    $rootConfiguration->getScenarioName()
                );

                throw new \RuntimeException($message);
            }

            $this->connection->getPDO()->rollBack();
        }
    }

}