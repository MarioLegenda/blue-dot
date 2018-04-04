<?php

namespace BlueDot\Kernel\Environment;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Kernel\Environment\Type\DevEnv;
use BlueDot\Kernel\Environment\Type\ProdEnv;

class EnvironmentFactory
{
    /**
     * @param string $env
     * @return TypeInterface
     */
    public static function create(string $env): TypeInterface
    {
        switch ($env) {
            case 'dev':
                return DevEnv::fromValue();
            case 'prod':
                return ProdEnv::fromValue();
            default:
                $message = sprintf(
                    'Invalid environment. Environments are: %s',
                    implode(', ', array_keys(EnvironmentTypes::getTypes()))
                );

                throw new \RuntimeException($message);
        }
    }
}