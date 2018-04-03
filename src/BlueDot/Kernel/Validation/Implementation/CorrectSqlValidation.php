<?php

namespace BlueDot\Kernel\Validation\Implementation;

use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Kernel\Validation\ValidatorInterface;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;

class CorrectSqlValidation implements ValidatorInterface
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
            $this->validateSimpleSqlType();
        }
    }
    /**
     * @throws \RuntimeException
     */
    private function validateSimpleSqlType() {
        $sqlType = $this->configuration->getMetadata()->getSqlType();
        $sql = $this->configuration->getWorkConfig()->getSql();
        $fullStatementName = $this->configuration->getMetadata()->getResolvedStatementName();

        $typeMatch = preg_match('#^[a-zA-Z]+\s#i', $sql, $matches);

        if ($typeMatch === 0) {
            $message = sprintf(
                'Sql type could not be determined from sql \'%s\' for statement \'%s\'',
                $sql,
                $fullStatementName
            );

            throw new \RuntimeException($message);
        }

        $matchedType = trim(strtolower($matches[0]));

        if ($matchedType !== (string) $sqlType) {
            $message = sprintf(
                'Sql type does not match the statement declaration in sql \'%s\' for statement \'%s\'',
                $sql,
                $fullStatementName
            );

            throw new \RuntimeException($message);
        }
    }
}