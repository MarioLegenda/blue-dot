<?php

namespace BlueDot\Kernel\Validation;

interface ValidatorInterface
{
    /**
     * @void
     * @throws \RuntimeException
     */
    public function validate();
}