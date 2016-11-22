<?php

namespace Test;

require __DIR__.'/../../vendor/autoload.php';

use BlueDot\BlueDot;
use BlueDot\Entity\Entity;

class MainTest extends \PHPUnit_Framework_TestCase
{
    public function testMain()
    {
        $blueDot = new BlueDot(__DIR__.'/configuration.yml');

        $blueDot->execute('simple.insert.insert_user', array(
            'name' => array('Mile', 'Mirko', 'Mirza'),
            'lastname' => array('Milutinovic', 'Mirkovilovic', 'Mirzic'),
            'occupation' => array('mason', 'pernar', 'reptile'),
        ))->getResult();

        $singleCity = $blueDot->execute('simple.select.single_city', array(
            'name' => 'Kabul',
            'country_code' => 'AFG',
        ))->getResult();

        $this->assertInstanceOf(Entity::class, $singleCity, 'simple.select.single_city does not return a '.Entity::class.' instance');
        $this->assertEquals('Kabul', $singleCity->get('Name'), 'simple.select.single_city does not return correct value for column \'Name\'. \'Kabul\' expected');
        $this->assertEquals('AFG', $singleCity->get('CountryCode', 'simple.select.single_city does not return correct value for column \'CountryCode\'. \'AFG\' expected'));
        $this->assertEquals('Kabol', $singleCity->get('District'), 'simple.select.single_city does not return correct value for column \'District\'. Expected \'Kabol\'');

        $insertVillage = $blueDot->execute('simple.insert.single_village', array(
            'name' => 'Kucine',
            'country' => 'Split',
        ))->getResult();

        $this->assertTrue($insertVillage->has('last_insert_id'), 'simple.insert.single_village should return an '.Entity::class.' with entry \'last_insert_id\'');

        $updateVillage = $blueDot->execute('simple.update.single_village', array(
            'update_name' => 'Makarska',
            'id' => 1,
        ))->getResult();

        $this->assertTrue($updateVillage->isEmpty(), 'simple.update.single_city should return an empty result');

        $blueDot->execute('simple.delete.single_village', array(
            'id' => 7,
        ));

        $scenarioResult = $blueDot->execute('scenario.insert_user', array(
            'select_user' => array(
                'id' => 1,
            ),
            'insert_user' => array(
                'occupation' => 'Inventor',
            ),
            'insert_address' => array(
                'city' => 'Split',
                'address' => 'Vinodolska 44',
            )
        ))->getResult();

        $this->assertInstanceOf(Entity::class, $scenarioResult, 'scenario.insert_user should return an instance of '.Entity::class);

        $this->assertTrue($scenarioResult->has('id'), 'scenario.insert_user should return a column \'id\'');
        $this->assertInternalType('int', (int) $scenarioResult->get('id'), 'scenario.insert_user should return a numeric \'id\'');

        $this->assertTrue($scenarioResult->has('user_name'), 'scenario.insert_user should return a column \'user_name\'');
        $this->assertInternalType('string', $scenarioResult->get('user_name'), 'scenario.insert_user should return a string from \'user_name\'');

        $this->assertTrue($scenarioResult->has('user_lastname'), 'scenario.insert_user should return a column \'user_lastname\'');
        $this->assertInternalType('string', $scenarioResult->get('user_lastname'), 'scenario.insert_user should return a string from \'user_lastname\'');

        $this->assertTrue($scenarioResult->has('get_address_by_id'), 'scenario.insert_user should return a column \'get_address_by_id\'');
        $this->assertInstanceOf(Entity::class, $scenarioResult->get('get_address_by_id'), 'scenario.insert_user should have an instance of '.Entity::class.' under \'get_address_by_id\'');

        $address = $scenarioResult->get('get_address_by_id');
    }
}