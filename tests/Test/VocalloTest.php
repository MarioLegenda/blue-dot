<?php

namespace Test;

use BlueDot\BlueDot;

class VocalloTest extends \PHPUnit_Framework_TestCase
{
    public function testDatabase()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/vocallo_user_db.yml');

        $languages = $blueDot->execute('simple.select.find_all_languages', array(
            'user_id' => 1,
        ))->getResult();
    }
}