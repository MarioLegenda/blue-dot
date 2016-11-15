<?php

namespace Test;

require __DIR__.'/../../vendor/autoload.php';

use BlueDot\BlueDot;
use BlueDot\Database\ParameterCollection;
use BlueDot\Entity\Entity;
use BlueDot\Entity\EntityCollection;

class MainTest extends \PHPUnit_Framework_TestCase
{
    public function testMain()
    {
        $blueDot = new BlueDot(__DIR__.'/configuration.yml');

        $result = $blueDot->executeSimple('single_city', array(
            'name' => 'Kabul',
            'country_code' => 'AFG',
        ));

        $this->assertInstanceOf(Entity::class, $result, '$result should be an instance of '.Entity::class);

        $this->assertEquals('Kabul', $result->get('Name'), 'Expected Kabul as result');

        $collection = $blueDot->executeSimple('entire_world');

        $this->assertInstanceOf(EntityCollection::class, $collection, '$collection should be an instance of '.EntityCollection::class);

        foreach ($collection as $collectionResult) {
            $this->assertInstanceOf(Entity::class, $collectionResult, '$collectionResult should be an instance of '.Entity::class);
            $this->assertInternalType('string', $collectionResult->get('Name'), '$collectionResult->get() should return a string');
        }

        $singleResult = $collection->findOneBy('Name', 'Kabul');

        $this->assertInstanceOf(Entity::class, $singleResult, 'Collection::findOneBy should return an instance of '.Entity::class);
        $this->assertEquals('Kabul', $singleResult->get('Name'), '$singleResult::get(Name) should return Kabul');

        $blueDot->executeSimple('insert.single_village', array(
            'name' => 'Jarmina',
        ));

        $parametersCollection = new ParameterCollection();
        $parametersCollection
            ->add('name', 'Mirkovci')
            ->add('name', 'Èokadinci')
            ->add('name', 'Harkanovci');

        $blueDot->executeSimple('insert.single_village', $parametersCollection);

        $blueDot->executeSimple('update.single_city', array(
            'id' => 1,
            'update_name' => 'Mislovarovci',
        ));

        $blueDot->executeSimple('delete.single_city', array(
            'id' => 6,
        ));

        $blueDot->executeCompound('insert_user');
    }
}