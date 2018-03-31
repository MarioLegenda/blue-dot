<?php

namespace BlueDot\Database\Execution\Validation\Implementation;

use BlueDot\Configuration\Flow\FlowConfigurationProductInterface;
use BlueDot\Configuration\Flow\Scenario\Metadata;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Database\Execution\Validation\ValidatorInterface;
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
            $sqlType = $this->configuration->getMetadata()->getSqlType();
            $sql = $this->configuration->getWorkConfig()->getSql();

            $this->validateCorrectSqlType($sqlType, $sql);
        }
    }
    /**
     * @param string $sqlType
     * @param string $sql
     * @throws \RuntimeException
     */
    private function validateCorrectSqlType(
        string $sqlType,
        string $sql
    ) {
        $regex = sprintf('#^%s#i', $sqlType);

        preg_match($regex, $sql, $match);

        if (empty($match)) {
            $message = sprintf(
                'Invalid sql type. \'%s\' is a statement under \'%s\' but is not a \'%s\' statement for sql: \'%s\'',
                $this->configuration->getName(),
                $this->configuration->getMetadata()->getResolvedStatementType(),
                $this->configuration->getMetadata()->getSqlType(),
                $this->configuration->getWorkConfig()->getSql()
            );

            throw new \RuntimeException($message);
        }
    }
}