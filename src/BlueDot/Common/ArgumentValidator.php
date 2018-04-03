<?php

namespace BlueDot\Common;

use BlueDot\Exception\BlueDotRuntimeException;

class ArgumentValidator implements ValidatorInterface
{
    /**
     * @param string|object $argument
     * @return ValidatorInterface
     * @throws BlueDotRuntimeException
     */
    public function validate($argument) : ValidatorInterface
    {
        $argc = explode('.', $argument);

        if (count($argc) === 2) {
            return $this;
        }

        if (count($argc) === 3) {
            return $this;
        }

        $message = sprintf(
            'Invalid execute statement name. Given \'%s\'',
            $argument
        );

        throw new BlueDotRuntimeException($message);
    }
}