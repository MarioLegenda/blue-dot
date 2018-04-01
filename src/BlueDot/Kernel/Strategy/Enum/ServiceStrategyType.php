<?php

namespace BlueDot\Kernel\Strategy\Enum;

use BlueDot\Common\Enum\BaseType;
use BlueDot\Common\Enum\TypeInterface;

class ServiceStrategyType extends BaseType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'service_strategy',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'service_strategy'): TypeInterface
    {
        return parent::fromValue($value);
    }
}