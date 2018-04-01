<?php

namespace BlueDot\Database\Parameter;

class UserParametersConverter
{
    /**
     * @var UserParametersConverter $instance
     */
    private static $instance;
    /**
     * @return UserParametersConverter
     */
    public static function instance()
    {
        static::$instance = (static::$instance instanceof static) ? static::$instance : new static();

        return static::$instance;
    }
    /**
     * @param array|null|object|object[] $userParameters
     * @param array $configParameters
     */
    public function convert($userParameters, array $configParameters)
    {

    }
}