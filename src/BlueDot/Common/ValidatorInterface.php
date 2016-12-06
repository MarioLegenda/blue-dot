<?php

namespace BlueDot\Common;

interface ValidatorInterface
{
    /**
     * @param $validationArgument
     * @return ValidatorInterface
     */
    public function setValidationArgument($validationArgument) : ValidatorInterface;
    /**
     * @return ValidatorInterface
     */
    public function validate() : ValidatorInterface;
}