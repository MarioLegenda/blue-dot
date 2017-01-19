<?php

namespace Test;

use BlueDot\BlueDot;

class ReblDatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testRebl()
    {
        $blueDot = new BlueDot(__DIR__.'/config/rebl_db_config.yml');

        $blueDot->execute('scenario.database');

        $blueDot->execute('simple.insert.insert_account', array(
            'type' => array(
                'free',
                'trial',
                'paid_monthly',
                'paid_yearly',
            )
        ));

        $blueDot->execute('scenario.create_user', array(
            'select_account' => array(
                'type' => 'trial',
            ),
            'create_user' => array(
                'username' => 'mile@gmail.com',
                'password' => 'budala',
            ),
            'create_role' => array(
                'role' => array(
                    'ROLE_TRIAL',
                    'ROLE_PAID_USER',
                ),
            )
        ));
    }
}