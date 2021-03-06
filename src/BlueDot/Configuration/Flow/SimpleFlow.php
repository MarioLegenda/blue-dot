<?php

namespace BlueDot\Configuration\Flow;

use BlueDot\Configuration\Filter\ByColumn;
use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Filter\FindExact;
use BlueDot\Configuration\Filter\NormalizeIfOneExists;
use BlueDot\Configuration\Filter\NormalizeJoinedResult;
use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Flow\Simple\Metadata;
use BlueDot\Configuration\Flow\Simple\Model;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Configuration\Flow\Simple\WorkConfig;

class SimpleFlow
{
    /**
     * @param string $resolvedStatementName
     * @param array $config
     * @param ImportCollection $importCollection
     * @return FlowConfigurationProductInterface|SimpleConfiguration
     * @throws \RuntimeException
     */
    public function create(
        string $resolvedStatementName,
        array $config,
        ImportCollection $importCollection
    ): FlowConfigurationProductInterface {
        $statementInfo = $this->resolveStatementInfo(
            $resolvedStatementName,
            $config
        );

        $sql = $this->resolveSql($importCollection, $config['sql']);
        $parameters = $this->resolveConfigParametersIfExists($config);
        $model = $this->resolveModelIfExists($config);
        $filter = $this->resolveFilter($config);

        $metadata = new Metadata(
            $statementInfo['statement_type'],
            $statementInfo['sql_type'],
            $statementInfo['statement_name'],
            $statementInfo['resolved_statement_type'],
            $statementInfo['resolved_statement_name']
        );

        $workConfig = new WorkConfig(
            $sql,
            $filter,
            $parameters,
            $model
        );

        return new SimpleConfiguration(
            $resolvedStatementName,
            $metadata,
            $workConfig
        );
    }
    /**
     * @param string $fullStatementName
     * @param array $config
     * @return array
     */
    private function resolveStatementInfo(
        string $fullStatementName,
        array $config
    ): array{
        $splittedNames = preg_split('#\.#', $fullStatementName);

        $statementType = $splittedNames[0];
        $sqlType = $splittedNames[1];
        $statementName = $splittedNames[2];

        return [
            'statement_type' => $statementType,
            'sql_type' => $sqlType,
            'statement_name' => $statementName,
            'resolved_statement_type' => sprintf('%s.%s', $statementType, $sqlType),
            'resolved_statement_name' => $fullStatementName,
        ];
    }
    /**
     * @param array $config
     * @return Model|null
     * @throws \RuntimeException
     */
    private function resolveModelIfExists(array $config): ?Model
    {
        $model = null;

        if (array_key_exists('model', $config)) {
            $object = $config['model']['object'];
            $properties = (array_key_exists('properties', $config['model'])) ? $config['model']['properties'] : array();

            $model = new Model($object, $properties);
        }

        return $model;
    }
    /**
     * @param ImportCollection $importCollection
     * @param string $sql
     * @return string
     */
    private function resolveSql(
        ImportCollection $importCollection,
        string $sql
    ): string {
        if ($importCollection->hasImport('sql_import')) {
            $import = $importCollection->getImport('sql_import');

            if ($import->hasValue($sql)) {
                return $import->getValue($sql);
            }
        }

        return $sql;
    }
    /**
     * @param array $config
     * @return array|null
     */
    private function resolveConfigParametersIfExists(array $config): ?array
    {
        if (array_key_exists('parameters', $config)) {
            return $config['parameters'];
        }

        return null;
    }
    /**
     * @param array $config
     * @return Filter
     */
    private function resolveFilter(array $config): ?Filter
    {
        if (!array_key_exists('filter', $config)) {
            return null;
        }

        $filters = $config['filter'];

        $resolvedFilters = [];
        foreach ($filters as $filterName => $filterData) {
            if ($filterName === 'by_column') {
                $resolvedFilters[] = new ByColumn(
                    $filterData,
                    'extractColumn'
                );
            }

            if ($filterName === 'find') {
                $column = $filterData[0];
                $value = $filterData[1];

                $resolvedFilters[] = new FindExact(
                    $column,
                    $value,
                    'find'
                );
            }

            if ($filterName === 'normalize_if_one_exists') {
                $resolvedFilters[] = new NormalizeIfOneExists('normalizeIfOneExists');
            }

            if ($filterName === 'normalize_joined_result') {
                $linkingColumn = $filterData['linking_column'];
                $columns = $filterData['columns'];

                $resolvedFilters[] = new NormalizeJoinedResult(
                    $linkingColumn,
                    $columns,
                    'normalizeJoinedResult'
                );
            }
        }

        return new Filter($resolvedFilters);
    }
}