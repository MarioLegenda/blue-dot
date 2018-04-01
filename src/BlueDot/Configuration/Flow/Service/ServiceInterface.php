<?php

namespace BlueDot\Configuration\Flow\Service;

interface ServiceInterface
{
    /**
     * @return array
     */
    public function run(): array;
}