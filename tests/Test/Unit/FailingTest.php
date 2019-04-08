<?php


namespace Test\Unit;

use BlueDot\BlueDot;
use BlueDot\Exception\ConfigurationException;


class FailingTest extends BaseTest
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
        $config = realpath(__DIR__ . '/../config/invalid/empty_configuration.yml');

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
        static::assertEquals($message, "Invalid configuration. Configuration file $config is empty");
    }
}