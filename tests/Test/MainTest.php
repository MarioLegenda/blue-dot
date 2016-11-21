<?php

namespace Test;

require __DIR__.'/../../vendor/autoload.php';

use BlueDot\BlueDot;

class MainTest extends \PHPUnit_Framework_TestCase
{
    public function testMain()
    {
        $blueDot = new BlueDot(__DIR__.'/configuration.yml');

        $blueDot->execute('scenario.insert_user', array(
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
        ));
/*
        $entity = $blueDot->execute('simple.select.entire_world')->getResult();

        $blueDot->execute('simple.insert.single_village', array(
            'name' => array(
                'Solin',
                'Omiš',
                'Dugopolje',
            ),
            'country' => array(
                'Split',
                'Zagreb',
                'Osijek',
            )
        ));*/
    }
}