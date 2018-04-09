<?php

namespace BlueDot\Configuration\Flow\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class SingleParameterType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'single_parameter',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'single_parameter'): TypeInterface
    {
        return parent::fromValue($value);
    }
}