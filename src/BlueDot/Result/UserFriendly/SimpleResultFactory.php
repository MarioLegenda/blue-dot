<?php

namespace BlueDot\Result\UserFriendly;

use BlueDot\Configuration\Flow\Simple\Enum\DeleteSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\InsertSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\OtherSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\SelectSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\UpdateSqlType;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Entity\Entity;
use BlueDot\Kernel\Result\KernelResultInterface;

class SimpleResultFactory
{
    /**
     * @var SimpleResultFactory $instance
     */
    private static $instance;
    /**
     * @return SimpleResultFactory
     */
    public static function instance()
    {
        static::$instance = (static::$instance instanceof static) ? static::$instance : new static();

        return static::$instance;
    }
    /**
     * @param KernelResultInterface $kernelResult
     * @return Entity
     */
    public function create(KernelResultInterface $kernelResult): Entity
    {
        /** @var SimpleConfiguration $configuration */
        $configuration = $kernelResult->getConfiguration();

        $sqlType = $configuration->getMetadata()->getSqlType();
        $kernelResult = $kernelResult->getResult();
        $resolvedStatementName = $configuration->getMetadata()->getResolvedStatementName();

        if ($sqlType->equals(InsertSqlType::fromValue())) {
            $result = [
                'sql_type' => (string) $sqlType,
                'last_insert_id' => (int) $kernelResult['last_insert_id'],
                'row_count' => (int) $kernelResult['row_count'],
            ];

            return new Entity(
                $result,
                $resolvedStatementName
            );
        }

        if ($sqlType->equals(SelectSqlType::fromValue())) {
            $result = [
                'sql_type' => (string) $sqlType,
                'row_count' => (int) $kernelResult['row_count'],
                'data' => $kernelResult['data'],
            ];

            return new Entity(
                $result,
                $resolvedStatementName
            );
        }

        if ($sqlType->equals(UpdateSqlType::fromValue())) {
            $result = [
                'sql_type' => (string) $sqlType,
                'row_count' => (int) $kernelResult['row_count'],
            ];

            return new Entity(
                $result,
                $resolvedStatementName
            );
        }

        if ($sqlType->equals(DeleteSqlType::fromValue())) {
            $result = [
                'sql_type' => (string) $sqlType,
                'row_count' => (int) $kernelResult['row_count'],
            ];

            return new Entity(
                $result,
                $resolvedStatementName
            );
        }

        if ($sqlType->equals(OtherSqlType::fromValue())) {
            $result = [
                'sql_type' => (string) $sqlType,
                'row_count' => (int) $kernelResult['row_count'],
            ];

            return new Entity(
                $result,
                $resolvedStatementName
            );
        }
    }
}