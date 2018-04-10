<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Configuration\Flow\Enum\MultipleParametersType;
use BlueDot\Configuration\Flow\Enum\SingleParameterType;
use BlueDot\Configuration\Flow\Simple\Enum\DeleteSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\InsertSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\OtherSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\SelectSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\UpdateSqlType;
use BlueDot\Configuration\Flow\Simple\Model;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Kernel\Connection\Connection;
use BlueDot\Kernel\Parameter\Parameter;
use BlueDot\Entity\Entity;
use BlueDot\Entity\ModelConverter;
use BlueDot\Kernel\Result\KernelCollectionResultConverter;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Kernel\Result\Simple\KernelResult;

class SimpleStrategy implements StrategyInterface
{
    /**
     * @var SimpleConfiguration $configuration
     */
    private $configuration;
    /**
     * @var Connection $connection
     */
    protected $connection;
    /**
     * SimpleStrategy constructor.
     * @param SimpleConfiguration $configuration
     * @param Connection $connection
     */
    public function __construct(
        SimpleConfiguration $configuration,
        Connection $connection
    ) {
        $this->configuration = $configuration;
        $this->connection = $connection;
    }
    /**
     * @inheritdoc
     */
    public function execute(bool $delayedTransactionCommit): KernelResultInterface
    {
        try {
            $this->connection->connect();

            if ($delayedTransactionCommit !== true) {
                $this->connection->getPDO()->beginTransaction();
            }

            $result = $this->doExecute();

            if ($delayedTransactionCommit !== true) {
                $this->connection->getPDO()->commit();
            }

            return $result;

        } catch (\PDOException $e) {
            if ($delayedTransactionCommit !== true) {
                $this->connection->getPDO()->rollBack();
            }

            $message = sprintf(
                'A PDOException was thrown for statement \'%s\' with message \'%s\'',
                $this->configuration->getMetadata()->getResolvedStatementName(),
                $e->getMessage()
            );

            throw new \RuntimeException($message);
        }
    }
    /**
     * @return KernelResultInterface
     */
    private function doExecute(): KernelResultInterface
    {
        $workConfig = $this->configuration->getWorkConfig();

        $sql = $workConfig->getSql();
        $userParameters = $workConfig->getUserParameters();
        $userParametersType = $workConfig->getUserParametersType();

        if (!$userParametersType instanceof TypeInterface) {
            return $this->singleExecute($sql, $userParameters);
        }

        if ($userParametersType->equals(SingleParameterType::fromValue())) {
            return $this->singleExecute($sql, $userParameters);
        }

        if ($userParametersType->equals(MultipleParametersType::fromValue())) {
            return $this->multipleExecute($sql, $userParameters);
        }
    }
    /**
     * @param string $sql
     * @param array $userParameters
     * @return KernelResultInterface
     */
    private function singleExecute(
        string $sql,
        array $userParameters
    ): KernelResultInterface {
        $pdoStatement = $this->connection->getPDO()->prepare($sql);

        if (!empty($userParameters)) {
            foreach ($userParameters as $key => $parameter) {
                $this->bindSingleParameter(new Parameter($key, $parameter), $pdoStatement);
            }
        }

        $pdoStatement->execute();

        $result = $this->getResult($pdoStatement);

        return $result;
    }
    /**
     * @param string $sql
     * @param array $userParameters
     * @return KernelResultInterface
     */
    private function multipleExecute(
        string $sql,
        array $userParameters
    ) {
        $kernelResultSet = [];
        foreach ($userParameters as $userParameter) {
            $kernelResultSet[] = $this->singleExecute($sql, $userParameter);
        }

        $kernelCollectionResultConverter = new KernelCollectionResultConverter(
            $this->configuration,
            $kernelResultSet
        );

        return $kernelCollectionResultConverter->convertToSingleKernelResult();
    }
    /**
     * @param \PDOStatement $pdoStatement
     * @return KernelResultInterface
     */
    public function getResult(\PDOStatement $pdoStatement): KernelResultInterface
    {
        /** @var TypeInterface $sqlType */
        $sqlType = $this->configuration->getMetadata()->getSqlType();

        if ($sqlType->equals(SelectSqlType::fromValue())) {
            $result = [];
            $queryResult = $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
            $rowCount = count($queryResult);

/*            if ($model instanceof Model) {
                $modelConverter = new ModelConverter($model, $queryResult);

                $converted = $modelConverter->convertIntoModel();

                $result['data'] = $converted;

                return new KernelResult(
                    $this->configuration,
                    $result
                );
            }*/

            $result['data'] = $queryResult;
            $result['row_count'] = $rowCount;

            return new KernelResult(
                $this->configuration,
                $result
            );
        }

        if ($sqlType->equals(InsertSqlType::fromValue())) {
            return $this->createInsertResult();
        }

        if ($sqlType->equals(UpdateSqlType::fromValue())) {
            $result = [];

            $result['data'] = [];
            $result['row_count'] = $pdoStatement->rowCount();

            return new KernelResult(
                $this->configuration,
                $result
            );
        }

        if ($sqlType->equals(DeleteSqlType::fromValue())) {
            $result = [];

            $result['data'] = [];
            $result['row_count'] = $pdoStatement->rowCount();

            return new KernelResult(
                $this->configuration,
                $result
            );
        }

        if ($sqlType->equals(OtherSqlType::fromValue())) {
            $result = [];

            $result['data'] = [];
            $result['row_count'] = $pdoStatement->rowCount();

            return new KernelResult(
                $this->configuration,
                $result
            );
        }
    }
    /**
     * @param Parameter $parameter
     * @param \PDOStatement $pdoStatement
     */
    private function bindSingleParameter(Parameter $parameter, \PDOStatement $pdoStatement)
    {
        $pdoStatement->bindValue(
            $parameter->getKey(),
            $parameter->getValue(),
            $parameter->getType()
        );
    }
    /**
     * @return KernelResultInterface
     */
    private function createInsertResult(): KernelResultInterface
    {
        $result = [];

        $result['data'] = [];
        $result['row_count'] = 1;
        $result['last_insert_id'] = (int) $this->connection->getPDO()->lastInsertId();

        return new KernelResult(
            $this->configuration,
            $result
        );
    }
}
