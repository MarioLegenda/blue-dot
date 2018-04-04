<?php

namespace BlueDot\Kernel\Environment\Type;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class DevEnv extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'dev',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'dev'): TypeInterface
    {
        return parent::fromValue($value);
    }
}