<?php

namespace BlueDot\Database\Validation;

interface ValidatorInterface
{
    /**
     * @void
     * @throws \RuntimeException
     */
    public function validate();
}