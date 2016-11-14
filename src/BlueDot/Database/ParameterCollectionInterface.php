<?php

namespace BlueDot\Database;

interface ParameterCollectionInterface
{
    public function add(array $parameter) : ParameterCollectionInterface;
}