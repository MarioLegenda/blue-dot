<?php

namespace BlueDot\Configuration\Flow;

use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Configuration\Flow\Simple\Metadata;
use BlueDot\Configuration\Flow\Simple\Model;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Configuration\Flow\Simple\WorkConfig;
use BlueDot\Exception\CompileException;

class SimpleFlow
{
    /**
     * @param string $resolvedStatementName
     * @param array $config
     * @param ImportCollection $importCollection
     * @return FlowConfigurationProductInterface
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function create(
        string $resolvedStatementName,
        array $config,
        ImportCollection $importCollection
    ): FlowConfigurationProductInterface {
        $statementInfo = $this->resolveStatementInfo($resolvedStatementName);

        $sql = $this->resolveSql($importCollection, $config['sql']);
        $parameters = $this->resolveConfigParametersIfExists($config);
        $model = $this->resolveModelIfExists($config);

        $metadata = new Metadata(
            $statementInfo['statement_type'],
            $statementInfo['sql_type'],
            $statementInfo['statement_name'],
            $statementInfo['resolved_statement_type'],
            $statementInfo['resolved_statement_name']
        );

        $workConfig = new WorkConfig(
            $sql,
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
     * @return array
     */
    private function resolveStatementInfo(string $fullStatementName): array
    {
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
     * @throws \BlueDot\Exception\BlueDotRuntimeException
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
            $import = $importCollection->getImport('sql_import', $sql);

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
}