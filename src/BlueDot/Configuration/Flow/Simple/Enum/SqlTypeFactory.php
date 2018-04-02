<?php

namespace BlueDot\Configuration\Flow\Simple\Enum;

use BlueDot\Common\Enum\TypeInterface;

class SqlTypeFactory
{
    /**
     * @param string $value
     * @return TypeInterface
     * @throws \RuntimeException
     */
    public static function getType(string $value): TypeInterface
    {
        $sqlTypes = SqlTypes::instance()->toArray();

        if (!array_key_exists($value, $sqlTypes)) {
            $message = sprintf(
                'Type \'%s\' could not be found',
                (string) $value
            );

            throw new \RuntimeException($message);
        }

        return $sqlTypes[$value]::{'fromValue'}();
    }
}