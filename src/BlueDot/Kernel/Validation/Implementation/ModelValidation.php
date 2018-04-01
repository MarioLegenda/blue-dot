<?php

namespace BlueDot\Kernel\Validation\Implementation;

use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Simple\Model;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Kernel\Validation\ValidatorInterface;

class ModelValidation implements ValidatorInterface
{
    /**
     * @var FlowConfigurationProductInterface|SimpleConfiguration|ScenarioConfiguration $configuration
     */
    private $configuration;
    /**
     * CorrectSqlValidation constructor.
     * @param FlowConfigurationProductInterface $configuration
     */
    public function __construct(FlowConfigurationProductInterface $configuration)
    {
        $this->configuration = $configuration;
    }
    /**
     * @inheritdoc
     */
    public function validate()
    {
        if ($this->configuration instanceof SimpleConfiguration) {
            $model = $this->configuration->getWorkConfig()->getModel();

            if ($model instanceof Model) {
                $this->validateModel($model);
            }
        }
    }
    /**
     * @param Model $model
     * @throws \RuntimeException
     */
    private function validateModel(Model $model)
    {
        $class = $model->getClass();

        if (!class_exists($class)) {
            $message = sprintf(
                'Provided model class \'%s\' does not exist for statement \'%s\'',
                $class,
                $this->configuration->getMetadata()->getResolvedStatementName()
            );

            throw new \RuntimeException($message);
        }

        if (!empty($properties)) {
            foreach ($properties as $statementColumn => $methodName) {
                if (!is_string($statementColumn)) {
                    $message = sprintf(
                        'Invalid model options. \'properties\' should be an associative array with {statement_name}.{column} as key and a model setter method as value. \'%s\' given for value \'%s\'',
                        $statementColumn,
                        $methodName
                    );

                    throw new \RuntimeException($message);
                }

                if (!method_exists($class, $methodName)) {
                    $message = sprintf(
                        'Invalid model options. \'properties\' should be an associative array with {statement_name}.{column} as key and a model setter method as value. Method \'%s\' does not exist for statement \'%s\'',
                        $methodName,
                        $this->configuration->getMetadata()->getResolvedStatementName()
                    );

                    throw new \RuntimeException($message);
                }
            }
        }
    }
}