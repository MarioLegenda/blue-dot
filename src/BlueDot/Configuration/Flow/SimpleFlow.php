<?php

namespace BlueDot\Configuration\Flow;

use BlueDot\Configuration\Import\ImportCollection;
use BlueDot\Database\Model\Metadata;
use BlueDot\Database\Model\Model;
use BlueDot\Database\Model\Simple\SimpleConfiguration;
use BlueDot\Database\Model\WorkConfig;
use BlueDot\Exception\CompileException;

class SimpleFlow
{
    /**
     * @param string $resolvedStatementName
     * @param array $config
     * @param ImportCollection $importCollection
     * @return FlowProductInterface
     * @throws CompileException
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    public function create(
        string $resolvedStatementName,
        array $config,
        ImportCollection $importCollection
    ): FlowProductInterface {
        $statementInfo = $this->resolveStatementInfo($resolvedStatementName);

        $metadata = new Metadata(
            $statementInfo['type'],
            $statementInfo['statement_type'],
            $statementInfo['statement_name'],
            $statementInfo['resolved_statement_name']
        );

        $sql = $config['sql'];
        $parameters = null;
        $model = null;

        if ($importCollection->hasImport('sql_import')) {
            $import = $importCollection->getImport('sql_import', $sql);

            if ($import->hasValue($sql)) {
                $sql = $import->getValue($sql);
            }
        }

        if (array_key_exists('parameters', $config)) {
            $parameters = $config['parameters'];
        }

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
            'type' => $statementType,
            'statement_type' => sprintf('%s.%s', $statementType, $sqlType),
            'statement_name' => $statementName,
            'resolved_statement_name' => $fullStatementName,
        ];
    }
}