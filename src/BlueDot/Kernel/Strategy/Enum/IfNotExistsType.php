<?php

namespace BlueDot\Kernel\Strategy\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class IfNotExistsType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'if_not_exists',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'if_not_exists'): TypeInterface
    {
        return parent::fromValue($value);
    }
}