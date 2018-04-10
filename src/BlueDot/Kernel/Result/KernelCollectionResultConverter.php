<?php

namespace BlueDot\Kernel\Result;

use BlueDot\Common\FlowProductInterface;
use BlueDot\Common\Util\Util;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Simple\Enum\DeleteSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\InsertSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\SelectSqlType;
use BlueDot\Configuration\Flow\Simple\Enum\UpdateSqlType;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Kernel\Result\Simple\KernelResult;

class KernelCollectionResultConverter
{
    /**
     * @var FlowProductInterface|SimpleConfiguration|ScenarioConfiguration $configuration
     */
    private $configuration;
    /**
     * @var KernelResultInterface[] $kernelResultSet
     */
    private $kernelResultSet;
    /**
     * KernelCollectionResultConverter constructor.
     * @param FlowProductInterface|SimpleConfiguration|ScenarioConfiguration $configuration
     * @param KernelResultInterface[] $kernelResultSet
     */
    public function __construct(
        FlowProductInterface $configuration,
        array $kernelResultSet
    ) {
        $this->configuration = $configuration;
        $this->kernelResultSet = $kernelResultSet;
    }
    /**
     * @return KernelResultInterface
     */
    public function convertToSingleKernelResult(): KernelResultInterface
    {
        $kernelResultSetGenerator = Util::instance()->createGenerator($this->kernelResultSet);

        $sqlType = $this->configuration->getMetadata()->getSqlType();
        if ($this->configuration instanceof SimpleConfiguration) {

            $data = [
                'row_count' => 0,
            ];

            foreach ($kernelResultSetGenerator as $item) {
                /** @var KernelResultInterface $kernelResult */
                $kernelResult = $item['item'];

                $result = $kernelResult->getResult();

                if ($sqlType->equals(InsertSqlType::fromValue())) {
                    $data['inserted_ids'][] = $result['last_insert_id'];
                    $data['last_insert_id'] = $result['last_insert_id'];
                    $data['row_count'] += (int) $result['row_count'];
                }

                if (
                    $sqlType->equals(UpdateSqlType::fromValue()) or
                    $sqlType->equals(DeleteSqlType::fromValue())
                ) {
                    $data['row_count'] += (int) $result['row_count'];
                }

                if ($sqlType->equals(SelectSqlType::fromValue())) {
                    $data['row_count'] += (int) $result['row_count'];

                    $data['data'][] = $result['data'];
                }
            }

            return new KernelResult(
                $this->configuration,
                $data
            );
        }
    }
}