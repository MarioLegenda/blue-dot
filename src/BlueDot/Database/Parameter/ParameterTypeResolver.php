<?php

namespace BlueDot\Database\Parameter;

class ParameterTypeResolver
{
    /**
     * @var ParameterTypeResolver $instance
     */
    private static $instance;
    /**
     * @return ParameterTypeResolver
     */
    public static function instance()
    {
        static::$instance = (static::$instance instanceof static) ? static::$instance : new static();

        return static::$instance;
    }
}