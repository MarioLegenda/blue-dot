<?php

namespace BlueDot\Result\UserFriendly;

use BlueDot\Common\Util\Util;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Entity\Entity;
use BlueDot\Kernel\Result\KernelResultInterface;
use BlueDot\Result\DeleteQueryResult;
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
     * @return Entity
     */
    public function create(KernelResultInterface $kernelResult): Entity
    {
        $result = Util::instance()->createGenerator($kernelResult->getResult());
        /** @var ScenarioConfiguration $configuration */
        $configuration = $kernelResult->getConfiguration();

        $scenarioName = $configuration->getRootConfiguration()->getScenarioName();

        $builtResult = [];

        foreach ($result as $item) {
            $queryResult = $item['item'];

            if ($queryResult instanceof InsertQueryResult) {
                $data = [
                    'row_count' => $queryResult->getRowCount(),
                    'last_insert_id' => (int) $queryResult->getLastInsertId(),
                ];

                $builtResult[$item['key']] = $data;
            }

            if ($queryResult instanceof DeleteQueryResult) {
                $data = [
                    'row_count' => $queryResult->getRowCount(),
                    'last_insert_id' => (int) $queryResult->getLastInsertId(),
                ];

                $builtResult[$item['key']] = $data;
            }

            if ($queryResult instanceof UpdateQueryResult) {
                $data = [
                    'row_count' => $queryResult->getRowCount(),
                ];

                $builtResult[$item['key']] = $data;
            }

            if ($queryResult instanceof SelectQueryResult) {
                $data = [
                    'row_count' => $queryResult->getMetadata()->getRowCount(),
                    'data' => $queryResult->getQueryResult(),
                ];

                $builtResult[$item['key']] = $data;
            }

            if ($queryResult instanceof NullQueryResult) {
                $data = null;

                $builtResult[$item['key']] = $data;
            }
        }

        return new Entity($builtResult, $scenarioName);
    }
}