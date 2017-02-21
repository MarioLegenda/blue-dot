<?php

namespace Test;

use BlueDot\BlueDot;

class VocalloTest extends \PHPUnit_Framework_TestCase
{
    public function testDatabase()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/vocallo_user_db.yml');

        $info = $blueDot->execute('scenario.insert_word', array(
            'insert_word' => array(
                'language_id' => 1,
                'word' => 'some word',
                'type' => 'some type',
            ),
            'insert_translation' => array(
                'translation' => array('translation 1', 'translation 2'),
            ),
            'insert_word_category' => null,
            'insert_word_image' => null,
        ))->getResult();

        $blueDot->execute('simple.update.schedule_word_removal', array(
            'word_id' => $info->get('insert_word')->get('last_insert_id'),
        ));

        $result = $blueDot->execute('scenario.remove_word', array(
            'remove_translations' => array(
                'word_id' => $info->get('insert_word')->get('last_insert_id'),
            ),
            'remove_word' => array(
                'word_id' => $info->get('insert_word')->get('last_insert_id'),
            ),
            'remove_word_category' => array(
                'word_id' => $info->get('insert_word')->get('last_insert_id'),
            ),
            'remove_word_image' => array(
                'word_id' => $info->get('insert_word')->get('last_insert_id')
            )
        ))->getResult();

        $blueDot->execute('scenario.create_course', array(
            'create_course' => array(
                'name' => 'Some name',
            ),
        ))->getResult();



        $translations = $blueDot->execute('simple.select.generic_injectable_sql', array(
            'injected_sql' => sprintf('SELECT word_id, translation FROM translations WHERE word_id IN (1, 60, 150, 78, 345)'),
        ))->getResult();

        $id = '60';
        $result = $translations->extract('translation', function($row) use ($id) {
            return $row['word_id'] === $id;
        });

        var_dump($result);
        die();
    }
}