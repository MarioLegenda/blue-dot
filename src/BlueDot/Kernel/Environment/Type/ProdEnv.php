<?php

namespace BlueDot\Kernel\Environment\Type;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class ProdEnv extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'prod',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'prod'): TypeInterface
    {
        return parent::fromValue($value);
    }
}