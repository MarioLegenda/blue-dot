<?php

namespace BlueDot\Kernel\Strategy\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class IfExistsType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'if_exists',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'if_exists'): TypeInterface
    {
        return parent::fromValue($value);
    }
}