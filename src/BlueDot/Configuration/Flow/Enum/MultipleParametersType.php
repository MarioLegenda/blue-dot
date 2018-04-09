<?php

namespace BlueDot\Configuration\Flow\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class MultipleParametersType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'multiple_parameter',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'multiple_parameter'): TypeInterface
    {
        return parent::fromValue($value);
    }
}