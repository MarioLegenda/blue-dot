<?php

namespace BlueDot\Database\Execution\Validation;

class ValidationResolver implements ValidationResolverInterface
{
    /**
     * @var ValidatorInterface[] $validators
     */
    private $validators = [];
    /**
     * @inheritdoc
     */
    public function addValidator(ValidatorInterface $validator): ValidationResolverInterface
    {
        $this->validators[] = $validator;

        return $this;
    }
    /**
     * @inheritdoc
     */
    public function resolveValidation()
    {
        /** @var ValidatorInterface $validator */
        foreach ($this->validators as $validator) {
            $validator->validate();
        }
    }
}