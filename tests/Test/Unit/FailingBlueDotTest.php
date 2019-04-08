<?php


namespace Test\Unit;


use BlueDot\BlueDot;
use BlueDot\Entity\PromiseInterface;
use PHPUnit\Framework\TestCase;

class FailingBlueDotTest extends TestCase
{
    public function test_blue_dot_multiple_calls_to_public_methods_fail()
    {
        $configSource = __DIR__.'/../config/result/prepared_execution_test.yml';

        $blueDot = new BlueDot();

        $blueDot->setConfiguration($configSource);

        $exceptionEntered = false;
        try {
            $blueDot->setConfiguration($configSource);
        } catch (\RuntimeException $e) {
            $exceptionEntered = true;
        }

        static::assertTrue($exceptionEntered);
    }
}