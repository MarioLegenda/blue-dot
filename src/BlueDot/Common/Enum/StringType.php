<?php

namespace BlueDot\Common\Enum;

class StringType extends BaseType
{
    /**
     * @param mixed $value
     * @return TypeInterface
     */
    public static function fromValue($value): TypeInterface
    {
        if (!is_string($value)) {
            $message = sprintf(
                'Invalid type. Created type has to be a string'
            );

            throw new \RuntimeException($message);
        }

        return parent::fromValue($value);
    }
}