<?php

namespace Test;

use BlueDot\BlueDot;

class VocalloTest extends \PHPUnit_Framework_TestCase
{
    public function testDatabase()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/vocallo_user_db.yml');
/*
        $blueDot->execute('scenario.database');
        $blueDot->execute('scenario.seed');*/

/*        $blueDot->execute('scenario.insert_word', array(
            'insert_word' => array(
                'user_id' => 1,
                'language_id' => 1,
                'word' => 'some word',
                'type' => 'some type',
            ),
            'insert_translation' => array(
                'translation' => array('translation 1', 'translation 2'),
            ),
            'insert_word_category' => null,
        ));*/
/*
        $blueDot->execute('scenario.remove_word', array(
            'remove_translations' => array(
                'word_id' => 1,
            ),
            'remove_word' => array(
                'word_id' => 1,
                'user_id' => 1
            ),
            'remove_word_category' => array(
                'word_id' => 1,
                'user_id' => 1
            ),
        ))->getResult();*/

        $result = $blueDot->execute('simple.select.testing_joins')->getResult();

        var_dump($result->arrangeMultiples(array(
            'translation',
        )));
        die();
    }
}