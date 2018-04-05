<?php

namespace BlueDot\Configuration\Flow;

use BlueDot\Common\Util\Util;
use BlueDot\Configuration\Filter\ByColumn;
use BlueDot\Configuration\Filter\Filter;
use BlueDot\Configuration\Filter\FindExact;
use BlueDot\Configuration\Filter\NormalizeIfOneExists;
use BlueDot\Configuration\Filter\NormalizeJoinedResult;
use BlueDot\Configuration\Flow\Scenario\ForeignKey;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Scenario\RootConfiguration;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Scenario\ScenarioModel;
use BlueDot\Configuration\Flow\Scenario\ScenarioReturnEntity;
use BlueDot\Configuration\Flow\Scenario\UseOption;
use BlueDot\Configuration\Import\ImportCollection;

class ScenarioFlow
{
    /**
     * @param string $scenarioName
     * @param array $config
     * @param ImportCollection $importCollection
     * @return ScenarioConfiguration
     */
    public function create(
        string $scenarioName,
        array $config,
        ImportCollection $importCollection
    ): ScenarioConfiguration {

        $resolvedScenarioName = sprintf('scenario.%s', $scenarioName);
        $scenarioModel = $this->resolveScenarioModelIfExists($config);
        $atomic = $config['atomic'];

        $rootConfiguration = new RootConfiguration(
            $resolvedScenarioName,
            $atomic,
            $scenarioModel
        );

        $metadata = $this->resolveStatementsMetadata(
            $rootConfiguration,
            $importCollection,
            Util::instance()->createGenerator($config['statements'])
        );

        return new ScenarioConfiguration(
            $rootConfiguration,
            $metadata
        );
    }
    /**
     * @param RootConfiguration $rootConfiguration
     * @param ImportCollection $importCollection
     * @param \Generator $statements
     * @return array
     */
    private function resolveStatementsMetadata(
        RootConfiguration $rootConfiguration,
        ImportCollection $importCollection,
        \Generator $statements
    ): array {
        $metadata = [];

        foreach ($statements as $statement) {
            $statementName = $statement['key'];
            $actualStatement = $statement['item'];
            $resolvedStatementName = sprintf('%s.%s', $rootConfiguration->getScenarioName(), $statementName);

            $sql = $this->resolveSql($actualStatement, $importCollection);
            $sqlType = $this->resolveSqlType($sql, $resolvedStatementName);
            $canBeEmptyResult = $this->resolveCanBeEmptyResult($actualStatement);
            $ifExistsStatementName = $this->resolveIfExistsStatementIfExists($actualStatement);
            $ifNotExistsStatementName = $this->resolveIfNotExistsStatementIfExists($actualStatement);
            $configParameters = $this->resolveConfigParametersIfExists($actualStatement);
            $useOption = $this->resolveUseOptionIfExists($actualStatement);
            $foreignKey = $this->resolveForeignKeyIfExists($actualStatement);
            $filter = $this->resolveFilters($actualStatement);

            $metadata[$statementName] = new Metadata(
                $resolvedStatementName,
                $sql,
                $sqlType,
                $canBeEmptyResult,
                $ifExistsStatementName,
                $ifNotExistsStatementName,
                null,
                $configParameters,
                $useOption,
                $foreignKey,
                $filter
            );
        }

        return $metadata;
    }
    /**
     * @param array $config
     * @return Filter|null
     */
    private function resolveFilters(array $config): ?Filter
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
    /**
     * @param array $statement
     * @return UseOption|null
     */
    private function resolveUseOptionIfExists(array $statement): ?UseOption
    {
        if (array_key_exists('use', $statement)) {
            $useOption = $statement['use'];

            return new UseOption($useOption['statement_name'], $useOption['values']);
        }

        return null;
    }
    /**
     * @param array $statement
     * @return ForeignKey|null
     */
    private function resolveForeignKeyIfExists(array $statement): ?ForeignKey
    {
        if (array_key_exists('foreign_key', $statement)) {
            $foreignKey = $statement['foreign_key'];

            return new ForeignKey($foreignKey['statement_name'], $foreignKey['bind_to']);
        }

        return null;
    }
    /**
     * @param array $statement
     * @return array|null
     */
    private function resolveConfigParametersIfExists(array $statement): ?array
    {
        if (array_key_exists('parameters', $statement)) {
            return $statement['parameters'];
        }

        return null;
    }
    /**
     * @param array $statement
     * @return string|null
     */
    private function resolveIfExistsStatementIfExists(array $statement): ?string
    {
        if (array_key_exists('if_exists', $statement)) {
            return $statement['if_exists'];
        }

        return null;
    }
    /**
     * @param array $statement
     * @return string|null
     */
    private function resolveIfNotExistsStatementIfExists(array $statement): ?string
    {
        if (array_key_exists('if_not_exists', $statement)) {
            return $statement['if_not_exists'];
        }

        return null;
    }
    /**
     * @param array $statement
     * @param ImportCollection $importCollection
     * @return string
     */
    private function resolveSql(
        array $statement,
        ImportCollection $importCollection
    ): string {
        if ($importCollection->hasImport('sql_import')) {
            $possibleImport = $statement['sql'];
            $import = $importCollection->getImport('sql_import');

            if ($importCollection->hasValue($possibleImport)) {
                return $import->getValue($possibleImport);
            }
        }

        return $statement['sql'];
    }
    /**
     * @param string $sql
     * @param string $resolvedStatementName
     * @return string
     */
    private function resolveSqlType(
        string $sql,
        string $resolvedStatementName
    ): string {
        preg_match('#(\w+\s)#i', $sql, $matches);

        if (empty($matches)) {
            throw new \RuntimeException(sprintf(
                'Sql syntax could not be determined for statement %s. Sql: %s. This could be because you use sql_import and misspelled this one',
                $resolvedStatementName,
                $sql
            ));
        }

        $sqlType = trim(strtolower($matches[1]));

        if ($sqlType === 'create' or $sqlType === 'use' or $sqlType === 'drop') {
            $sqlType = 'table';
        }

        if ($sqlType === 'modify' or $sqlType === 'alter') {
            $sqlType = 'update';
        }

        return $sqlType;
    }
    /**
     * @param array $statement
     * @return bool
     */
    private function resolveCanBeEmptyResult(array $statement): bool
    {
        if (array_key_exists('can_be_empty_result', $statement)) {
            return $statement['can_be_empty_result'];
        }

        return false;
    }
    /**
     * @param array $config
     * @return ScenarioModel|null
     */
    private function resolveScenarioModelIfExists(array $config): ?ScenarioModel
    {
        if (array_key_exists('scenario_model', $config)) {
            $scenarioModel = $config['scenario_model'];

            $class = $scenarioModel['class'];
            $binders = (array_key_exists('binders', $scenarioModel)) ? $scenarioModel['binders'] : null;

            return new ScenarioModel(
                $class,
                $binders
            );
        }

        return null;
    }
}