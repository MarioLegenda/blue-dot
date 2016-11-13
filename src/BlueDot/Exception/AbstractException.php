<?php

namespace BlueDot\Exception;

abstract class AbstractException extends \Exception
{
    /**
     * @constructor
     * @param $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }
}