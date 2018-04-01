<?php

namespace BlueDot\Database\Validation;

interface ValidationResolverInterface
{
    /**
     * @param ValidatorInterface $validator
     * @return ValidationResolverInterface
     */
    public function addValidator(ValidatorInterface $validator): ValidationResolverInterface;
    /**
     * @void
     * @throws \RuntimeException
     */
    public function resolveValidation();
}