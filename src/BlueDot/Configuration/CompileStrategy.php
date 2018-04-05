<?php

namespace BlueDot\Configuration;

class CompileStrategy
{
    /**
     * @var CompileStrategy $instance
     */
    private static $instance;

    private $compilers = [];

    public static function instance(): CompileStrategy
    {
        static::$instance = (static::$instance instanceof static) ? static::$instance : new static();

        return static::$instance;
    }

    public function getStrategy()
    {

    }

    private function getSimpleCompiler(): ConfigurationCompilerInterface
    {

    }
}