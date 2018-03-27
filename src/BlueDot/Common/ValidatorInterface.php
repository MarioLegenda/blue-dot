<?php

namespace BlueDot\Common;

use BlueDot\Database\Model\ConfigurationInterface;

interface ValidatorInterface
{
    /**
     * @param object|string $configuration
     * @return ValidatorInterface
     */
    public function validate($configuration) : ValidatorInterface;
}