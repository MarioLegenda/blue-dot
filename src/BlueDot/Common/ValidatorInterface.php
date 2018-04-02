<?php

namespace BlueDot\Common;

interface ValidatorInterface
{
    /**
     * @param object|string $configuration
     * @return ValidatorInterface
     */
    public function validate($configuration) : ValidatorInterface;
}