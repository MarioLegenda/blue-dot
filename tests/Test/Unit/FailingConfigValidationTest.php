<?php


namespace Test\Unit;

use BlueDot\Configuration\Validator\ConfigurationValidator;
use BlueDot\Exception\ConfigurationException;
use PHPUnit\Framework\TestCase;

class FailingConfigValidationTest extends TestCase
{
    public function test_empty_connection_key()
    {
        $config = [
            'configuration' => [
                'connection' => []
            ]
        ];

        $exceptionEntered = false;
        $message = null;
        try {
            $configValidator = new ConfigurationValidator($config);

            $configValidator->validate();
        } catch (ConfigurationException $e) {
            $exceptionEntered = true;
            $message = $e->getMessage();
        }

        static::assertTrue($exceptionEntered);
        static::assertInternalType('string', $message);
        static::assertTrue((bool) preg_match('#Node has to be a non empty array#', $message));
    }

    public function test_invalid_connection_key()
    {
        $config = [
            'configuration' => [
                'connection' => [
                    'invalid'
                ]
            ]
        ];

        $exceptionEntered = false;
        $message = null;
        try {
            $configValidator = new ConfigurationValidator($config);

            $configValidator->validate();
        } catch (ConfigurationException $e) {
            $exceptionEntered = true;
            $message = $e->getMessage();
        }

        static::assertTrue($exceptionEntered, 'ConfigurationException has not been thrown');
        static::assertInternalType('string', $message);
        static::assertTrue((bool) preg_match('#does not exist for parent node \'connection\'#', $message), 'Failed asserting the right exception message');

        $mandatoryKeys = ['host', 'user', 'password'];

        $variations = (function() use ($mandatoryKeys) {
            $variations = [];

            foreach ($mandatoryKeys as $key) {
                $variations[] = [$key => null];
            }

            $len = count($mandatoryKeys);
            for ($i = 0; $i < $len; $i++) {
                for ($a = 0; $a < $len; $a++) {
                    if ($mandatoryKeys[$i] !== $mandatoryKeys[$a]) {
                        $variations[] = [$mandatoryKeys[$i] => null, $mandatoryKeys[$a] => null];
                    }
                }
            }

            $variations[] = (function() use ($mandatoryKeys) {
                $temp = [];

                foreach ($mandatoryKeys as $key) {
                    $temp[$key] = null;
                }

                return $temp;
            })();

            return $variations;
        })();

        foreach ($variations as $variation) {
            $config = [
                'configuration' => [
                    'connection' => $variation
                ]
            ];

            $exceptionEntered = false;
            $message = null;
            try {
                $configValidator = new ConfigurationValidator($config);

                $configValidator->validate();
            } catch (ConfigurationException $e) {
                $exceptionEntered = true;
                $message = $e->getMessage();
            }

            static::assertTrue($exceptionEntered, 'ConfigurationException has not been thrown');
            static::assertInternalType('string', $message);

        }
    }

    public function test_scenario_key_not_empty()
    {
        $scenarioInvalidValues = [[], null, 'invalid', 0, 1234];

        foreach ($scenarioInvalidValues as $val) {
            $config = [
                'configuration' => [
                    'scenario' => $val,
                ]
            ];

            $exceptionEntered = false;
            $message = null;
            try {
                $configValidator = new ConfigurationValidator($config);

                $configValidator->validate();
            } catch (ConfigurationException $e) {
                $exceptionEntered = true;
                $message = $e->getMessage();
            }

            static::assertTrue($exceptionEntered, 'ConfigurationException has not been thrown');
            static::assertInternalType('string', $message);
        }
    }

    public function test_service_key_not_empty()
    {
        $scenarioInvalidValues = [[], null, 'invalid', 0, 1234];

        foreach ($scenarioInvalidValues as $val) {
            $config = [
                'configuration' => [
                    'service' => $val,
                ]
            ];

            $exceptionEntered = false;
            $message = null;
            try {
                $configValidator = new ConfigurationValidator($config);

                $configValidator->validate();
            } catch (ConfigurationException $e) {
                $exceptionEntered = true;
                $message = $e->getMessage();
            }

            static::assertTrue($exceptionEntered, 'ConfigurationException has not been thrown');
            static::assertInternalType('string', $message);
        }
    }

    public function test_service_key()
    {
        $scenarioInvalidValues = [[], null, 0, 1234];

        foreach ($scenarioInvalidValues as $val) {
            $config = [
                'configuration' => [
                    'service' => [
                        'class' => $val
                    ]
                ]
            ];

            $exceptionEntered = false;
            $message = null;
            try {
                $configValidator = new ConfigurationValidator($config);

                $configValidator->validate();
            } catch (ConfigurationException $e) {
                $exceptionEntered = true;
                $message = $e->getMessage();
            }

            static::assertTrue($exceptionEntered, 'ConfigurationException has not been thrown');
            static::assertInternalType('string', $message);
        }
    }
}