<?php

namespace BlueDot\Result\UserFriendly;

use BlueDot\Common\Util\Util;
use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Flow\Enum\MultipleParametersType;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Entity\BaseEntity;
use BlueDot\Entity\EntityCollection;
use BlueDot\Entity\EntityInterface;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Result\DeleteQueryResult;
use BlueDot\Result\FilterApplier;
use BlueDot\Result\InsertQueryResult;
use BlueDot\Result\NullQueryResult;
use BlueDot\Result\SelectQueryResult;
use BlueDot\Result\UpdateQueryResult;

class ScenarioResultFactory
{
    /**
     * @var ScenarioResultFactory $instance
     */
    private static $instance;
    /**
     * @return ScenarioResultFactory
     */
    public static function instance()
    {
        static::$instance = (static::$instance instanceof static) ? static::$instance : new static();

        return static::$instance;
    }
    /**
     * @param KernelResultInterface $kernelResult
     * @param FilterApplier $filterApplier
     * @return EntityCollection
     */
    public function create(
        KernelResultInterface $kernelResult,
        FilterApplier $filterApplier
    ): EntityCollection {
        $result = Util::instance()->createGenerator($kernelResult->getResult());
        /** @var ScenarioConfiguration $configuration */
        $configuration = $kernelResult->getConfiguration();

        /** @var Metadata[] $metadata */
        $metadata = $configuration->getMetadata();

        $scenarioName = $configuration->getRootConfiguration()->getScenarioName();

        $builtResult = [];

        foreach ($result as $item) {
            $queryResult = $item['item'];

            if (is_array($queryResult)) {
                foreach ($queryResult as $result) {
                    $this->buildResult(
                        $result,
                        $item['key'],
                        $metadata,
                        $filterApplier,
                        $builtResult
                    );
                }
            }

            $this->buildResult(
                $queryResult,
                $item['key'],
                $metadata,
                $filterApplier,
                $builtResult
            );
        }

        return new EntityCollection($scenarioName, $builtResult);
    }
    /**
     * @param Filter|null $filter
     * @param FilterApplier $filterApplier
     * @param BaseEntity $entity
     * @return BaseEntity
     */
    private function applyFilter(
        BaseEntity $entity,
        FilterApplier $filterApplier,
        Filter $filter = null
    ): BaseEntity {
        if ($filter instanceof Filter) {
            $appliedFilterEntity = $filterApplier->apply($entity, $filter);

            $data = $appliedFilterEntity->toArray();
            $name = $entity->getName();

            $data['row_count'] = $entity->getRowCount();

            return new BaseEntity($name, $data);
        }

        return $entity;
    }
    /**
     * @param $queryResult
     * @param string $statementName
     * @param Metadata[] $metadata
     * @param FilterApplier $filterApplier
     * @param $builtResult
     */
    private function buildResult(
        $queryResult,
        string $statementName,
        array $metadata,
        FilterApplier $filterApplier,
        &$builtResult
    ) {
        $singleStatementName = $statementName;
        /** @var Metadata $statementMetadata */
        $statementMetadata = $metadata[$singleStatementName];

        if ($queryResult instanceof InsertQueryResult) {
            $data = [
                'row_count' => $queryResult->getRowCount(),
                'last_insert_id' => (int) $queryResult->getLastInsertId(),
                'data' => null,
                'type' => 'scenario',
            ];

            $userParametersType = $statementMetadata->getUserParametersType();

            if ($userParametersType instanceof MultipleParametersType) {
                $alreadyBuiltResult = [
                    'row_count' => 0,
                ];

                if (array_key_exists($statementName, $builtResult)) {
                    $alreadyBuiltResult = $builtResult[$statementName];
                }

                $alreadyBuiltResult['last_insert_id'] = (int) $queryResult->getLastInsertId();
                $alreadyBuiltResult['row_count'] += (int) $queryResult->getRowCount();
                $alreadyBuiltResult['inserted_ids'][] = (int) $queryResult->getLastInsertId();

                $builtResult[$statementName] = $alreadyBuiltResult;

                return;
            }

            $builtResult[$statementName] = $data;
        }

        if ($queryResult instanceof DeleteQueryResult) {
            $data = [
                'row_count' => $queryResult->getRowCount(),
            ];

            $userParametersType = $statementMetadata->getUserParametersType();

            if ($userParametersType instanceof MultipleParametersType) {
                $alreadyBuiltResult = [];

                if (array_key_exists($statementName, $builtResult)) {
                    $alreadyBuiltResult = $builtResult[$statementName];
                }

                $alreadyBuiltResult['row_count'] += (int) $queryResult->getRowCount();

                $builtResult[$statementName] = $alreadyBuiltResult;

                return;
            }

            $builtResult[$statementName] = $data;
        }

        if ($queryResult instanceof UpdateQueryResult) {
            $data = [
                'row_count' => $queryResult->getRowCount(),
            ];

            $userParametersType = $statementMetadata->getUserParametersType();

            if ($userParametersType instanceof MultipleParametersType) {
                $alreadyBuiltResult = [];

                if (array_key_exists($statementName, $builtResult)) {
                    $alreadyBuiltResult = $builtResult[$statementName];
                }

                $alreadyBuiltResult['row_count'] += (int) $queryResult->getRowCount();

                $builtResult[$statementName] = $alreadyBuiltResult;

                return;
            }

            $builtResult[$statementName] = $data;
        }

        if ($queryResult instanceof SelectQueryResult) {
            $data = [
                'row_count' => $queryResult->getMetadata()->getRowCount(),
                'data' => $queryResult->getQueryResult(),
            ];

            $filter = $statementMetadata->getFilter();

            $appliedFilterEntity = $this->applyFilter(
                new BaseEntity($data),
                $filterApplier,
                $filter
            );

            $userParametersType = $statementMetadata->getUserParametersType();

            if ($userParametersType instanceof MultipleParametersType) {
                if (array_key_exists($statementName, $builtResult)) {
                    $alreadyBuiltResult = $builtResult[$statementName];
                }

                $alreadyBuiltResult['data'][] = $appliedFilterEntity->toArray();

                $builtResult[$statementName] = $alreadyBuiltResult;

                return;
            }

            $builtResult[$statementName] = $appliedFilterEntity->toArray();
        }

        if ($queryResult instanceof NullQueryResult) {
            $builtResult[$statementName] = null;
        }
    }
}