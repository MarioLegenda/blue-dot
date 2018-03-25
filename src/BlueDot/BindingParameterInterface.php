<?php

namespace BlueDot;

interface BindingParameterInterface
{
    /**
     * @return array
     */
    public function toBindingParameter(): array;
}