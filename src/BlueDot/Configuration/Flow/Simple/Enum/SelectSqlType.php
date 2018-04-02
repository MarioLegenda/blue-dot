<?php

namespace BlueDot\Configuration\Flow\Simple\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class SelectSqlType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'select',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'select'): TypeInterface
    {
        return parent::fromValue($value);
    }
}