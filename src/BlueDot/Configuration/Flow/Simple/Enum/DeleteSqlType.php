<?php

namespace BlueDot\Configuration\Flow\Simple\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class DeleteSqlType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'delete',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'delete'): TypeInterface
    {
        return parent::fromValue($value);
    }
}