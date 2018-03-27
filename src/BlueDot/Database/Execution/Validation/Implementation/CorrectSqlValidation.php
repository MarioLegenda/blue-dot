<?php

namespace BlueDot\Database\Execution\Validation\Implementation;

use BlueDot\Database\Execution\Validation\ValidatorInterface;
use BlueDot\Database\Model\ConfigurationInterface;
use BlueDot\Database\Model\Simple\SimpleConfiguration;

class CorrectSqlValidation implements ValidatorInterface
{
    /**
     * @var ConfigurationInterface|SimpleConfiguration $configuration
     */
    private $configuration;
    /**
     * CorrectSqlValidation constructor.
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $this->validateCorrectSqlType();
    }
    /**
     * @throw \RuntimeException
     */
    private function validateCorrectSqlType()
    {
        $metadata = $this->configuration->getMetadata();
        $workConfig = $this->configuration->getWorkConfig();

        $sqlType = $metadata->getStatementType();
        $sql = $workConfig->getSql();

        $regex = sprintf('#^%s#i', $sqlType);

        preg_match($regex, $sql, $match);

        if (empty($match)) {
            $message = sprintf(
                'Invalid sql type. \'%s\' is a statement under \'%s\' but is not a \'%s\' statement for sql: \'%s\'',
                $this->configuration->getName(),
                $metadata->getResolvedStatementType(),
                $metadata->getStatementType(),
                $workConfig->getSql()
            );

            throw new \RuntimeException($message);
        }
    }
}