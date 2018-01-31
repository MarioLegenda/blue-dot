<?php

namespace BlueDot\Common;

interface ValidatorInterface
{
    /**
     * @param string|object $argument
     * @return ValidatorInterface
     */
    public function validate($argument) : ValidatorInterface;
}