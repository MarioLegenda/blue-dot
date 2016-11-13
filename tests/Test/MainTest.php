<?php

namespace Test;

require __DIR__.'/../../vendor/autoload.php';

use BlueDot\BlueDot;
use BlueDot\Result\Result;
use BlueDot\Result\ResultCollection;

class MainTest extends \PHPUnit_Framework_TestCase
{
    public function testMain()
    {
        $blueDot = new BlueDot(__DIR__.'/configuration.yml');

        $result = $blueDot->executeSimple('single_city', array(
            'name' => 'Kabul',
            'country_code' => 'AFG',
        ));

        $this->assertInstanceOf(Result::class, $result, '$result should be an instance of '.Result::class);

        $this->assertEquals('Kabul', $result->get('Name'), 'Expected Kabul as result');

        $collection = $blueDot->executeSimple('entire_world');

        $this->assertInstanceOf(ResultCollection::class, $collection, '$collection should be an instance of '.ResultCollection::class);

        foreach ($collection as $collectionResult) {
            $this->assertInstanceOf(Result::class, $collectionResult, '$collectionResult should be an instance of '.Result::class);
        }
    }
}