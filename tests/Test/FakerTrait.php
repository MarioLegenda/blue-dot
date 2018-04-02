<?php

namespace Test;

use Faker\Factory;
use Faker\Generator;

trait FakerTrait
{
    /**
     * @var Generator $faker
     */
    private $faker;
    /**
     * @return Generator
     */
    public function getFaker(): Generator
    {
        if ($this->faker instanceof Generator) {
            return $this->faker;
        }

        $this->faker = Factory::create();

        return $this->faker;
    }
}