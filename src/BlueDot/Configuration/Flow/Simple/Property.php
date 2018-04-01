<?php

namespace BlueDot\Configuration\Flow\Simple;

use BlueDot\Exception\BlueDotRuntimeException;

class Property
{
    /**
     * @var string $statementName
     */
    private $statementName;
    /**
     * @var string $column
     */
    private $column;
    /**
     * @var string $modelProperty
     */
    private $modelProperty;
    /**
     * Property constructor.
     * @param string $statement
     * @param string $modelProperty
     * @throws \RuntimeException
     */
    public function __construct(string $statement, string $modelProperty)
    {
        $exploded = explode('.', $statement);

        if (count($exploded) !== 2) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid model property. Property should be a \'key:value\' pair with \'key\' as {statement_name}.{column_name} and \'value\' as the name of the model property'
                )
            );
        }

        $this->statementName = $exploded[0];
        $this->column = $exploded[1];
        $this->modelProperty = $modelProperty;
    }
    /**
     * @return string
     */
    public function getColumn() : string
    {
        return $this->column;
    }
    /**
     * @return string
     */
    public function getModelProperty() : string
    {
        return $this->modelProperty;
    }
}