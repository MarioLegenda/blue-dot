<?php

namespace Test;

require __DIR__ . '/../../vendor/autoload.php';

use BlueDot\BlueDot;
use BlueDot\Entity\Entity;

class UserInfoDatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testWorldDatabase()
    {
        $blueDot = BlueDot::instance(__DIR__ . '/config/user_info_db_config.yml');

        $blueDot->execute('scenario.create_database');
        $blueDot->execute('scenario.clear_database');
        $blueDot->execute('simple.insert.insert_city');

        $blueDot->execute('simple.insert.insert_user', array(
            'name' => array('Mile', 'Mirko', 'Mirza'),
            'lastname' => array('Milutinovic', 'Mirkovilovic', 'Mirzic'),
            'occupation' => array('mason', 'pernar', 'reptile'),
        ))->getResult();

        $singleCity = $blueDot->execute('simple.select.single_city', array(
            'name' => 'Kabul',
            'country_code' => 'AFG',
        ))->getResult();

        $this->assertInstanceOf(Entity::class, $singleCity, 'simple.select.single_city does not return a ' . Entity::class . ' instance');
        $this->assertEquals('Kabul', $singleCity->get('name'), 'simple.select.single_city does not return correct value for column \'name\'. \'Kabul\' expected');
        $this->assertEquals('AFG', $singleCity->get('country_code', 'simple.select.single_city does not return correct value for column \'country_code\'. \'AFG\' expected'));
        $this->assertEquals('Kabol', $singleCity->get('district'), 'simple.select.single_city does not return correct value for column \'district\'. Expected \'Kabol\'');

        $insertVillage = $blueDot->execute('simple.insert.single_village', array(
            'name' => 'Kucine',
            'country' => 'Split',
        ))->getResult();

        $this->assertTrue($insertVillage->has('last_insert_id'), 'simple.insert.single_village should return an ' . Entity::class . ' with entry \'last_insert_id\'');

        $updateVillage = $blueDot->execute('simple.update.single_village', array(
            'update_name' => 'Makarska',
            'id' => 1,
        ))->getResult();

        $this->assertTrue($updateVillage->isEmpty(), 'simple.update.single_city should return an empty result');

        $blueDot->execute('simple.delete.single_village', array(
            'id' => 7,
        ));

        $scenarioResult = $blueDot->execute('scenario.user_info_database', array(
            'insert_multi_users' => array(
                'name' => array('Miki', 'Miki', 'Miki'),
                'lastname' => array('Milutinovic', 'Mirkovilovic', 'Mirzic'),
                'occupation' => array('mason', 'pernar', 'reptile'),
            ),
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

        $this->assertInstanceOf(Entity::class, $scenarioResult, 'scenario.insert_user should return an instance of ' . Entity::class);

        $this->assertTrue($scenarioResult->has('id'), 'scenario.insert_user should return a column \'id\'');
        $this->assertInternalType('int', (int)$scenarioResult->get('id'), 'scenario.insert_user should return a numeric \'id\'');

        $this->assertTrue($scenarioResult->has('user_name'), 'scenario.insert_user should return a column \'user_name\'');
        $this->assertInternalType('string', $scenarioResult->get('user_name'), 'scenario.insert_user should return a string from \'user_name\'');

        $this->assertTrue($scenarioResult->has('user_lastname'), 'scenario.insert_user should return a column \'user_lastname\'');
        $this->assertInternalType('string', $scenarioResult->get('user_lastname'), 'scenario.insert_user should return a string from \'user_lastname\'');


        $this->assertTrue($scenarioResult->has('get_address_by_id'), 'scenario.insert_user should return a column \'get_address_by_id\'');
        $this->assertInstanceOf(Entity::class, $scenarioResult->get('get_address_by_id'), 'scenario.insert_user should have an instance of ' . Entity::class . ' under \'get_address_by_id\'');

        $address = $scenarioResult->get('get_address_by_id');

        $this->assertTrue($address->has('id'), 'scenario.insert_user.get_address_by_id result entity should contain \'id\'');
        $this->assertInternalType('int', (int)$address->get('id'), 'scenario.insert_user.get_address_by_id \'id\' should be an integer');

        $this->assertTrue($address->has('user_id'), 'scenario.insert_user.get_address_by_id result entity should contain \'user_id\'');
        $this->assertInternalType('int', (int)$address->get('user_id'), 'scenario.insert_user.get_address_by_id \'user_id\' should be an integer');

        $this->assertTrue($address->has('city'), 'scenario.insert_user.get_address_by_id result entity should contain \'city\'');
        $this->assertInternalType('string', $address->get('city'), 'scenario.insert_user.get_address_by_id \'city\' should be a string');

        $this->assertTrue($address->has('address'), 'scenario.insert_user.get_address_by_id result entity should contain \'address\'');
        $this->assertInternalType('string', $address->get('address'), 'scenario.insert_user.get_address_by_id \'address\' should be a string');

        $blueDot->execute('callable.validate_user');
    }
}