<?php

namespace Test;

use Faker\Factory;
use Test\Model\Language;
use BlueDot\Entity\PromiseInterface;
use Test\Model\Category;

class Seed
{
    /**
     * @var Seed $instance
     */
    private static $instance;
    /**
     * @return Seed
     */
    public static function instance()
    {
        self::$instance = (self::$instance instanceof self) ? self::$instance : new self();

        return self::$instance;
    }

    public function reset($blueDot)
    {
        $blueDot->execute('scenario.drop');
        $blueDot->execute('scenario.seed');
    }

}