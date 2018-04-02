<?php

namespace BlueDot\Configuration\Flow\Simple\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class InsertSqlType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'insert',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'insert'): TypeInterface
    {
        return parent::fromValue($value);
    }
}