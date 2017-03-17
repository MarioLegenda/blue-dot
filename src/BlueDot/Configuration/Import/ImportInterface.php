<?php

namespace BlueDot\Configuration\Import;

interface ImportInterface
{
    /**
     * @param string $value
     * @return bool
     */
    public function hasValue(string $value) : bool;
    /**
     * @param string $value
     * @return string
     */
    public function getValue(string $value) : string;
}