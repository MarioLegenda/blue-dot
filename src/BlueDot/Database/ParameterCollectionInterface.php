<?php

namespace BlueDot\Database;

interface ParameterCollectionInterface
{
    public function add(string $name, $value) : ParameterCollectionInterface;
}