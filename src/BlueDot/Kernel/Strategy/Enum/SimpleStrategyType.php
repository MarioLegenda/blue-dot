<?php

namespace BlueDot\Kernel\Strategy\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class SimpleStrategyType extends StringType
{
    protected static $types = [
        'simple_strategy',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'simple_strategy'): TypeInterface
    {
        return parent::fromValue($value);
    }
}