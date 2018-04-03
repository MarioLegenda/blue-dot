<?php

namespace BlueDot\Configuration\Flow\Simple\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class OtherSqlType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'other',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'other'): TypeInterface
    {
        return parent::fromValue($value);
    }
}