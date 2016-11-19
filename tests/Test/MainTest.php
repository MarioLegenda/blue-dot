<?php

namespace Test;

require __DIR__.'/../../vendor/autoload.php';

use BlueDot\BlueDot;

class MainTest extends \PHPUnit_Framework_TestCase
{
    public function testMain()
    {
        $blueDot = new BlueDot(__DIR__.'/configuration.yml');

        $entity = $blueDot->execute('simple.select.entire_world')->getResult();

        //var_dump($entity);
/*
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