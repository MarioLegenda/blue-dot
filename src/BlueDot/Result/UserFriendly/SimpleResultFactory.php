<?php

namespace BlueDot\Result\UserFriendly;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Flow\Enum\MultipleParametersType;
use BlueDot\Configuration\Flow\Simple\Enum\DeleteSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\InsertSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\OtherSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\SelectSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\UpdateSqlType;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Entity\Entity;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Result\FilterApplier;

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
     * @param FilterApplier $filterApplier
     * @return Entity
     */
    public function create(
        KernelResultInterface $kernelResult,
        FilterApplier $filterApplier
    ): Entity {
        /** @var SimpleConfiguration $configuration */
        $configuration = $kernelResult->getConfiguration();

        /** @var TypeInterface $userParametersType */
        $userParametersType = $configuration->getWorkConfig()->getUserParametersType();
        /** @var TypeInterface $sqlType */
        $sqlType = $configuration->getMetadata()->getSqlType();
        $kernelResult = $kernelResult->getResult();
        $resolvedStatementName = $configuration->getMetadata()->getResolvedStatementName();

        if ($sqlType->equals(InsertSqlType::fromValue())) {
            $result = [
                'sql_type' => (string) $sqlType,
                'last_insert_id' => (int) $kernelResult['last_insert_id'],
                'row_count' => (int) $kernelResult['row_count'],
            ];

            if ($userParametersType->equals(MultipleParametersType::fromValue())) {
                $result['inserted_ids'] = $kernelResult['inserted_ids'];
            }

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

            $filter = $configuration->getWorkConfig()->getFilter();

            $entity = new Entity(
                $result,
                $resolvedStatementName
            );

            return $this->applyFilter($entity, $filterApplier, $filter);
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
    /**
     * @param Filter|null $filter
     * @param FilterApplier $filterApplier
     * @param Entity $entity
     * @return Entity
     */
    private function applyFilter(
        Entity $entity,
        FilterApplier $filterApplier,
        Filter $filter = null
    ): Entity {
        if ($filter instanceof Filter) {
            $appliedFilterEntity = $filterApplier->apply($entity, $filter);

            $appliedFilterEntity->add('row_count', $entity->get('row_count'));

            return $appliedFilterEntity;
        }

        return $entity;
    }
}