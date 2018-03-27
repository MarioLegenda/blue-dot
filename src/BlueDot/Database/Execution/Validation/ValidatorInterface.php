<?php

namespace BlueDot\Database\Execution\Validation;

interface ValidatorInterface
{
    /**
     * @void
     * @throws \RuntimeException
     */
    public function validate();
}