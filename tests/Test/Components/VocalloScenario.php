<?php

namespace Test\Components;

use BlueDot\BlueDotInterface;
use BlueDot\Entity\PromiseInterface;

class VocalloScenario implements TestComponentInterface
{
    /**
     * @var $phpunit
     */
    private $phpunit;
    /**
     * @var BlueDotInterface $blueDot
     */
    private $blueDot;
    /**
     * VocalloScenario constructor.
     * @param \PHPUnit_Framework_Assert $phpunit
     * @param BlueDotInterface $blueDot
     */
    public function __construct(\PHPUnit_Framework_Assert $phpunit, BlueDotInterface $blueDot)
    {
        $this->phpunit = $phpunit;
        $this->blueDot = $blueDot;
    }

    public function run()
    {
        $info = $this->blueDot->execute('scenario.insert_word', array(
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
        ))->success(function(PromiseInterface $promise) {
            $lastInsertId = $promise->getResult()->get('insert_word')->get('last_insert_id');

            $this->blueDot->execute('simple.update.schedule_word_removal', array(
                'word_id' => $lastInsertId,
            ));

            $this->blueDot->execute('scenario.remove_word', array(
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
        });

        $this->blueDot->execute('scenario.create_course', array(
            'create_course' => array(
                'name' => 'Some name',
            ),
        ));

        $translations = $this->blueDot
            ->createStatementBuilder()
            ->addSql(sprintf('SELECT word_id, translation FROM translations WHERE word_id IN (1, 60, 150, 78, 345)'))
            ->execute()
            ->getResult();

        $id = '60';
        $translations->extract('translation', function($row) use ($id) {
            return $row['word_id'] === $id;
        });

        $this->blueDot->execute('simple.select.find_all_languages');

        $this->blueDot->execute('simple.select.find_lesson', array(
            'class_id' => 1,
            'name' => 'kreten',
        ))->success(function(PromiseInterface $promise) {
            return 'success';
        })->failure(function(PromiseInterface $promise) {
            return 'failure';
        })->getResult();

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
        ));

        $createTheoryDeck = $this->blueDot->execute('scenario.create_theory_deck', array(
            'create_sound' => array(
                array(
                    'relative_path' => 'korkut',
                    'absolute_path' => 'mile',
                    'file_name' => 'mario',
                    'relative_full_path' => 'idiot',
                    'absolute_full_path' => 'mrcina',
                ),
                array(
                    'relative_path' => 'andrea',
                    'absolute_path' => 'mile',
                    'file_name' => 'mario',
                    'relative_full_path' => 'andrea',
                    'absolute_full_path' => 'mrcina',
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
    }
}