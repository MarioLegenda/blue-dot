<?php

namespace Test;

use BlueDot\BlueDot;

class VocalloTest extends \PHPUnit_Framework_TestCase
{
    public function testDatabase()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/vocallo_user_db.yml');

        $blueDot->execute('scenario.database');
        $blueDot->execute('scenario.seed');

        $blueDot->execute('scenario.insert_word', array(
            'insert_word' => array(
                'user_id' => 1,
                'language_id' => 1,
                'word' => 'some word',
                'type' => 'some type',
            ),
            'insert_translation' => array(
                'translation' => array('translation 1', 'translation 2'),
            ),
            'insert_image' => array(
                'absolute_path' => 'sdlkfaslfd',
                'relative_path' => 'sdkfsalkdf',
                'file_name' => ' sdlkfsklflsjdf'
            )
        ));
    }
}