<?php

namespace BlueDot\Kernel\Environment;

use BlueDot\Kernel\Environment\Type\DevEnv;
use BlueDot\Kernel\Environment\Type\ProdEnv;

class EnvironmentTypes
{
    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            'dev' => DevEnv::class,
            'prod' => ProdEnv::class,
        ];
    }
}