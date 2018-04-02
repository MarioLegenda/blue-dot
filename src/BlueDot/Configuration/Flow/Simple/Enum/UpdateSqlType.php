<?php

namespace BlueDot\Configuration\Flow\Simple\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class UpdateSqlType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'update',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'update'): TypeInterface
    {
        return parent::fromValue($value);
    }
}