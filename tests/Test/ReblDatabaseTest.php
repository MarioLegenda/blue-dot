<?php

namespace Test;

use BlueDot\BlueDot;

class ReblDatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testReblSimpleStatements()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/rebl_db_config.yml');

        $blueDot->execute('scenario.database');

        $insertedIds = $blueDot->execute('simple.insert.insert_account', array(
            array('type' => 'anonymous'),
            array('type' => 'trial'),
            array('type' => 'paid_monthly'),
            array('type' => 'paid_yearly'),
        ))->getResult();

        $this->assertCount(4, $insertedIds, 'There should be 4 for inserts for statement simple.insert.insert_account');

        foreach ($insertedIds as $insertedId) {
            $this->assertInternalType('int', (int) $insertedId, 'Every insert should return an inserted id for statement simple.insert.insert_account');
        }

        $insertedIds = $blueDot->execute('simple.insert.insert_user', array(
            array(
                'username' => 'Konj',
                'password' => 'digital1986',
                'account_id' => '2',
            ),
            array(
                'username' => 'Mirko',
                'password' => 'digital1986',
                'account_id' => '6',
            )
        ))->getResult();

        $this->assertCount(2, $insertedIds, 'There should be 2 for inserts for statement simple.insert.insert_user');

        foreach ($insertedIds as $insertedId) {
            $this->assertInternalType('int', (int) $insertedId, 'Every insert should return an inserted id for statement simple.insert.user');
        }

        $insertedIds = $blueDot->execute('simple.insert.insert_account', array(
            'type' => 'mile',
        ))->getResult();

        $this->assertCount(1, $insertedIds, 'There should be 1 inserts for statement simple.insert.insert_account');

        foreach ($insertedIds as $insertedId) {
            $this->assertInternalType('int', (int) $insertedId, 'Every insert should return an inserted id for statement simple.insert.insert_account');
        }

        $insertedIds = $blueDot->execute('simple.insert.insert_account', array(
            'type' => array(
                'mile',
                'kile',
                'domile',
            )
        ))->getResult();

        $this->assertCount(3, $insertedIds, 'There should be 3 inserts for statement simple.insert.insert_account');

        foreach ($insertedIds as $insertedId) {
            $this->assertInternalType('int', (int) $insertedId, 'Every insert should return an inserted id for statement simple.insert.insert_account');
        }

        $users = $blueDot->execute('simple.select.find_users', array(
            array('id' => 1),
            array('id' => 2),
        ))->getResult();

        $this->assertCount(2, $users, 'There should be 2 users returned for statement simple.select.find_users');

        foreach ($users as $user) {
            $this->assertInternalType('string', $user['username'], 'Users username should be a string');
            $this->assertInternalType('string', $user['password'], 'Users password should be a string');
            $this->assertInternalType('string', $user['account_id'], 'Users account_id should be a string');
        }

        $user = $blueDot->execute('simple.select.find_users', array(
            'id' => 1,
        ))->getResult();

        $this->assertEquals('1', $user->get('id'), 'Expected value 1 for id for statement simple.select.find_users');
        $this->assertEquals('Konj', $user->get('username'), 'Expected value Mile for statement simple.select.find_users');
        $this->assertEquals('digital1986', $user->get('password'), 'Expected value digital1986 for password for simple.select.find_users');
        $this->assertEquals('2', $user->get('account_id'), 'Expected value 2 for password for simple.select.find_users');

        $rowCount = $blueDot->execute('simple.update.update_user', array(
            'username' => 'Miroslav',
            'id' => 1,
        ))->getResult();

        $this->assertEquals(1, $rowCount, 'After updating simple.update.update_user, row count should be 1');

        $user = $blueDot->execute('simple.select.find_users', array(
            'id' => 1,
        ))->getResult();

        $this->assertEquals('Miroslav', $user->get('username'), 'After updating simple.update.update_user, username should be Miroslav');

        $rowCount = $blueDot->execute('simple.delete.delete_user', array(
            'id' => 1,
        ))->getResult();

        $this->assertEquals(1, $rowCount, 'simple.delete.delete_user should return row count 1');

        $user = $blueDot->execute('simple.select.find_users', array(
            'id' => 1,
        ))->getResult();

        $this->assertNull($user, 'User should not exist for statement simple.select.find_user after deletion');
    }

    public function testReblScenarioStatements()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/rebl_db_config.yml');

        $result = $blueDot->execute('scenario.create_user', array(
            'select_account' => array(
                'type' => 'trial',
            ),
            'create_user' => array(
                'username' => 'stoga@gmail.com',
                'password' => 'budala',
            ),
            'create_role' => array(
                'role' => array(
                    'ROLE_TRIAL',
                    'ROLE_PAID_USER',
                ),
            )
        ))->getResult();

        $this->assertTrue($result->has('account_type'), 'Result of scenario.create_user should have an account_type field');
        $this->assertEquals($result->get('account_type'), 'trial', 'Result of scenario.create_user should have account_type field \'trial\' value');
    }
}