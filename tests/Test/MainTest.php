<?php

namespace Test;

require __DIR__.'/../../vendor/autoload.php';

use BlueDot\BlueDot;

class MainTest extends \PHPUnit_Framework_TestCase
{
    public function testMain()
    {
        $blueDot = new BlueDot(__DIR__.'/configuration.yml');

        $result = $blueDot->execute('callable.validate_user', array());
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