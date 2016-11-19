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

        $result = $blueDot->execute('simple.select.single_city', array(
            'name' => 'Kabul',
            'country_code' => 'AFG',
        ));

        $this->assertInstanceOf(Entity::class, $result, '$result should be an instance of '.Entity::class);

        $blueDot->execute('simple.insert.single_village', array(
            'name' => 'Harkanovci'
        ));
    }
}