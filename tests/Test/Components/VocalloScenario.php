<?php

namespace Test\Components;

use BlueDot\Entity\PromiseInterface;
use BlueDot\BlueDot;

class VocalloScenario extends AbstractTestComponent
{
    public function run()
    {
        $this->blueDot = new BlueDot(__DIR__ . '/../config/vocallo_user_db.yml');

        $this->blueDot->execute('callable.callable_service');

        $promise = $this->blueDot->execute('scenario.update_working_language', array(
            'find_working_language' => array(
                'user_id' => 1,
            ),
            'create_working_language' => array(
                'user_id' => 1,
                'language_id' => 1,
            ),
            'update_working_language' => array(
                'user_id' => 1,
                'language_id' => 1,
            ),
        ));

        $this->blueDot->execute('scenario.only_selects', array(
            'select_first_language' => array('id' => 1),
            'select_second_language' => array('id' => 2),
        ))
            ->success(function(PromiseInterface $promise) {
                $result = $promise->getResult()->toArray();

                $this->phpunit->assertArrayHasKey('select_first_language', $result, 'scenario.only_selects should have select_first_language');
                $this->phpunit->assertArrayHasKey('select_second_language', $result, 'scenario.only_selects should have select_second_language');

                $firstLanguage = $result['select_first_language'];
                $secondLanguage = $result['select_second_language'];

                $this->phpunit->assertNotEmpty($firstLanguage, 'select_first_language should not be empty');
                $this->phpunit->assertNotEmpty($secondLanguage, 'select_second_language should not be empty');
            })
            ->failure(function() {
                die("failure");
            });

        $insertWordPromise = $this->blueDot->execute('scenario.insert_word', array(
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
        ));

        if ($insertWordPromise->isSuccess()) {
            $result = $insertWordPromise->getResult();
            $lastInsertId = $result->get('insert_word')->get('last_insert_id');

            $this->phpunit->assertInternalType('int', (int) $lastInsertId, 'last_insert_id should be integer for scenario.insert_word.insert_word');

            $insertTranslationInfo = $result->get('insert_translation');

            $this->phpunit->assertInternalType('int', $insertTranslationInfo->get('last_insert_id'), 'last_insert_id for scenario.insert_word.insert_translation should be an integer');
            $this->phpunit->assertEquals(2, $insertTranslationInfo->get('row_count'), 'row_count for scenario.insert_word.insert_translation should be 2');

            $wordRemovalPromise = $this->blueDot->execute('simple.update.schedule_word_removal', array(
                'word_id' => $lastInsertId,
            ));

            $result = $wordRemovalPromise->getResult();

            $this->phpunit->assertEquals(1, $result->get('rows_affected'), 'rows_affected for simple.update.schedule_word_removal should be 1');

            $removeWordPromise = $this->blueDot->execute('scenario.remove_word', array(
                'remove_translations' => array(
                    'word_id' => $lastInsertId,
                ),
                'remove_word' => array(
                    'word_id' => $lastInsertId,
                ),
                'remove_word_category' => array(
                    'word_id' => $lastInsertId,
                ),
                'remove_word_image' => array(
                    'word_id' => $lastInsertId,
                ),
            ));

            $result = $removeWordPromise->getResult();

            $this->phpunit->assertEquals(2, $result->get('remove_translations')->get('row_count'), 'scenario.remove_word.remove_translation should only remove 2 rows');
            $this->phpunit->assertEquals(1, $result->get('remove_word')->get('row_count'), 'scenario.remove_word.remove_word should only remove 1 row');
        }

        $translations = $this->blueDot
            ->createStatementBuilder()
            ->addSql(sprintf('SELECT word_id, translation FROM translations WHERE word_id IN (1, 60, 150, 78, 345)'))
            ->execute()
            ->getResult();

        foreach ($translations as $translation) {
            $this->phpunit->assertArrayHasKey('word_id', $translation, 'Fetching translations with statement builder should return with key word_id');
            $this->phpunit->assertArrayHasKey('translation', $translation, 'Fetching translations with statement builder should return with key translation');
        }

        $id = '60';
        $translations->extract('translation', function($row) use ($id) {
            return $row['word_id'] === $id;
        });

        $this->blueDot->execute('simple.select.find_all_languages')
            ->failure(function() {
                $this->phpunit->fail('simple.select.find_all_languages failed');
            });

        $this->blueDot->execute('scenario.insert_word', array(
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
        ))
            ->failure(function(PromiseInterface $promise) {
                $this->phpunit->fail('scenario.insert_word failed');
            });


        $createTheoryDeck = $this->blueDot->execute('scenario.create_theory_deck', array(
            'create_sound' => array(
                array(
                    'relative_path' => 'korkut',
                    'absolute_path' => 'mile',
                    'file_name' => 'mario',
                    'relative_full_path' => 'idiot',
                    'absolute_full_path' => 'mrcina',
                    'client_original_name' => 'mile',
                ),
                array(
                    'relative_path' => 'andrea',
                    'absolute_path' => 'mile',
                    'file_name' => 'mario',
                    'relative_full_path' => 'andrea',
                    'absolute_full_path' => 'mrcina',
                    'client_original_name' => 'mile',
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
        ))->getResult();

        $createTheoryId = $createTheoryDeck->get('create_theory_deck')->get('last_insert_id');

        $parameters = array(
            'select_sounds' => array(
                'deck_id' => $createTheoryId,
            ),
            'remove_deck_sounds' => array(
                'deck_id' => $createTheoryId,
            ),
            'remove_theory_sounds' => array(
                'deck_id' => $createTheoryId,
            ),
            'create_sounds' => null,
            'update_theory_deck' => array(
                'deck_id' => $createTheoryId,
                'internal_name' => 'sdfjsajdg',
                'deck_data' => 'sjdkfjlsčakdjf',
                'internal_description' => 'sdjkfhsadjf',
                'show_on_page' => false,
                'ordering' => 6,
            ),
            'select_deck' => array(
                'deck_id' => $createTheoryId,
            ),
        );

        $this->blueDot->execute('scenario.update_theory_deck', $parameters)
            ->success(function(PromiseInterface $promise) {
                $filesToDelete = $promise->getResult()->get('files_to_delete');

                if (!is_string($filesToDelete) and !is_array($filesToDelete)) {
                    $this->phpunit->fail('scenario.update_theory_deck expects expects \'files_to_delete\' to be a string or an array');
                }
            })
            ->failure(function(PromiseInterface $promise) {
                $this->phpunit->fail('scenario.update_theory_deck failed');
            });

        $this->blueDot->execute('scenario.update_theory_deck_no_return_data', $parameters)
            ->failure(function() {
                $this->phpunit->fail('scenario.update_theory_deck_no_return_data failed');
            });
    }
}