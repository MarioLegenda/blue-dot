<?php

namespace BlueDot\Common;

use BlueDot\Kernel\Model\ConfigurationInterface;

interface ValidatorInterface
{
    /**
     * @param object|string $configuration
     * @return ValidatorInterface
     */
    public function validate($configuration) : ValidatorInterface;
}