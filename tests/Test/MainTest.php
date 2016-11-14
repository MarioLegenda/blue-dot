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
            $this->assertInternalType('string', $collectionResult->get('Name'), '$collectionResult->get() should return a string');
        }

        $singleResult = $collection->findOneBy('Name', 'Kabul');

        $this->assertInstanceOf(Result::class, $singleResult, 'Collection::findOneBy should return an instance of '.Result::class);
        $this->assertEquals('Kabul', $singleResult->get('Name'), '$singleResult::get(Name) should return Kabul');

        $blueDot->executeSimple('insert.single_city', array(
            'name' => 'Jarmina',
        ));

        $blueDot->executeSimple('update.single_city', array(
            'id' => 6,
            'update_name' => 'Mislovarovci',
        ));

        $blueDot->executeSimple('delete.single_city', array(
            'id' => 6,
        ));
    }
}