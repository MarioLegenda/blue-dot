<?php

namespace Test;

use BlueDot\BlueDot;
use BlueDot\Entity\PromiseInterface;

class VocalloTest extends \PHPUnit_Framework_TestCase
{
    public function testDatabase()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/vocallo_user_db.yml');
/*
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

        $promise = $blueDot->execute('simple.select.find_all_languages');*/
/*
        $result = $blueDot->execute('simple.select.find_lesson', array(
            'class_id' => 1,
            'name' => 'kreten',
        ))->success(function(PromiseInterface $promise) {
            return 'success';
        })->failure(function(PromiseInterface $promise) {
            return 'failure';
        })->getResult();*/

/*        $blueDot->execute('scenario.insert_word', array(
            'insert_word' => array(
                'language_id' => 1,
                'word' => 'dfasdfsadf',
                'type' => 'sdkjfslakdjfl',
            ),
            'insert_word_image' => null,
            'insert_translation' => array(
                'translation' => array('mile', 'mile', 'mile')
            ),
            'insert_word_category' => array(
                'category_id' => 5,
            ),
        ));*/

        $blueDot->execute('scenario.database');
        $blueDot->execute('scenario.seed');


        $blueDot->execute('scenario.create_theory_deck', array(
            'create_sound' => array(
                array(
                    'relative_path' => 'mario',
                    'absolute_path' => 'mario',
                    'file_name' => 'mario',
                    'relative_full_path' => 'idiot',
                    'absolute_full_path' => 'mario',
                ),
                array(
                    'relative_path' => 'mario',
                    'absolute_path' => 'mario',
                    'file_name' => 'mario',
                    'relative_full_path' => 'idiot',
                    'absolute_full_path' => 'mario',
                ),
                array(
                    'relative_path' => 'mario',
                    'absolute_path' => 'mario',
                    'file_name' => 'mario',
                    'relative_full_path' => 'idiot',
                    'absolute_full_path' => 'mario',
                ),
                array(
                    'relative_path' => 'mario',
                    'absolute_path' => 'mario',
                    'file_name' => 'mario',
                    'relative_full_path' => 'idiot',
                    'absolute_full_path' => 'mario',
                ),
            ),
            'create_theory_deck' => array(
                'theory_id' => 7,
                'internal_name' => 'dkfjdlsk',
                'deck_data' => 'dfasdfas',
                'internal_description' => 'člsjdkfklsdf',
                'show_on_page' => true,
                'ordering' => 7
            )
        ));



        $parameters = array(
            'select_sounds' => array(
                'deck_id' => 1,
            ),
            'remove_deck_sounds' => array(
                'deck_id' => 1,
            ),
            'remove_theory_sounds' => array(
                'deck_id' => 1,
            ),
            'create_sounds' => null,
            'update_theory_deck' => array(
                'deck_id' => 1,
                'internal_name' => 'sdfjsajdg',
                'deck_data' => 'sjdkfjlsčakdjf',
                'internal_description' => 'sdjkfhsadjf',
                'show_on_page' => false,
                'ordering' => 6,
            ),
            'select_deck' => array(
                'deck_id' => 1,
            ),
        );

        $promise = $blueDot->execute('scenario.update_theory_deck', $parameters)
            ->success(function(PromiseInterface $promise) {
                var_dump($promise->getOriginalEntity());
            })
            ->failure(function(PromiseInterface $promise) {
            })
            ->getResult();


        die("budala");
    }
}