<?php

namespace BlueDot\Result;

interface ResultInterface
{
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name);
}