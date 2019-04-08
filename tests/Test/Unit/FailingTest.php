<?php


namespace Test\Unit;

use BlueDot\BlueDot;
use BlueDot\Exception\ConfigurationException;
use PHPUnit\Framework\TestCase;


class FailingTest extends TestCase
{
    public function test_invalid_config_file()
    {
        $nonExistentConfig = 'non/existing/file.yml';

        $exceptionEntered = false;
        $message = null;
        try {
            $blueDot = new BlueDot($nonExistentConfig);
        } catch (\InvalidArgumentException $e) {
            $exceptionEntered = true;
            $message = $e->getMessage();
        }

        static::assertTrue($exceptionEntered);
        static::assertInternalType('string', $message);
        static::assertEquals($message, "The file $nonExistentConfig does not exist");
    }

    public function test_invalid_configuration_key()
    {
        $config = realpath(__DIR__ . '/../config/invalid/invalid_configuration_key.yml');

        $exceptionEntered = false;
        $message = null;
        try {
            $blueDot = new BlueDot($config);
        } catch (ConfigurationException $e) {
            $exceptionEntered = true;
            $message = $e->getMessage();
        }

        static::assertTrue($exceptionEntered);
        static::assertInternalType('string', $message);
        static::assertEquals($message, "Invalid configuration. The 'configuration' key/node does not exist");
    }
}