<?php

namespace BlueDot\Kernel\Strategy\Enum;

use BlueDot\Common\Enum\StringType;
use BlueDot\Common\Enum\TypeInterface;

class ScenarioStrategyType extends StringType
{
    /**
     * @var array $types
     */
    protected static $types = [
        'scenario_strategy',
    ];
    /**
     * @param string $value
     * @return TypeInterface
     */
    public static function fromValue($value = 'scenario_strategy'): TypeInterface
    {
        return parent::fromValue($value);
    }
}