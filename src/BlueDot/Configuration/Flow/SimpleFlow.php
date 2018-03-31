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
     * @throws CompileException
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function create(
        string $resolvedStatementName,
        array $config,
        ImportCollection $importCollection
    ): FlowConfigurationProductInterface {
        $statementInfo = $this->resolveStatementInfo($resolvedStatementName);

        $metadata = new Metadata(
            $statementInfo['statement_type'],
            $statementInfo['sql_type'],
            $statementInfo['statement_name'],
            $statementInfo['resolved_statement_type'],
            $statementInfo['resolved_statement_name']
        );

        $sql = $config['sql'];
        $parameters = null;
        $model = $this->resolveModelIfExists($config);

        if ($importCollection->hasImport('sql_import')) {
            $import = $importCollection->getImport('sql_import', $sql);

            if ($import->hasValue($sql)) {
                $sql = $import->getValue($sql);
            }
        }

        if (array_key_exists('parameters', $config)) {
            $parameters = $config['parameters'];
        }

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
     * @throws CompileException
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    private function resolveModelIfExists(array $config): ?Model
    {
        $model = null;

        if (array_key_exists('model', $config)) {
            $object = $config['model']['object'];
            $properties = (array_key_exists('properties', $config['model'])) ? $config['model']['properties'] : array();

            if (!class_exists($object)) {
                throw new CompileException(sprintf('Invalid model options. Object \'%s\' does not exist', $object));
            }

            if (!empty($properties)) {
                foreach ($properties as $key => $value) {
                    if (!is_string($key)) {
                        $message = sprintf(
                            'Invalid model options. \'properties\' should be an associative array with {statement_name}.{column} as key and a model property as value. %s given for value %s',
                            $key,
                            $value
                        );

                        throw new CompileException($message);
                    }
                }
            }

            $model = new Model($object, $properties);
        }

        return $model;
    }
}