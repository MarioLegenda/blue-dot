<?php

namespace BlueDot\Common\Util;

class Util
{
    /**
     * @var Util $instance
     */
    private static $instance;
    /**
     * @return Util
     */
    public static function instance(): Util
    {
        static::$instance = (static::$instance instanceof Util) ? static::$instance : new static();

        return static::$instance;
    }

    private function __construct() {}
    /**
     * @param array $array
     * @return \Generator
     */
    public function createGenerator(array $array): \Generator
    {
        foreach ($array as $key => $item) {
            yield ['key' => $key, 'item' => $item];
        }
    }
}