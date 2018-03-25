<?php

namespace BlueDot\Common;

interface CallableInterface
{
    /**
     * @return array
     */
    public function run(): array;
}