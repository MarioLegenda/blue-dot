<?php

namespace Test\Components;

use BlueDot\BlueDotInterface;
use BlueDot\Database\Connection;
use Test\Model\Language;
use BlueDot\Entity\PromiseInterface;
use Test\Model\Category;

require_once __DIR__.'/../../../vendor/fzaninotto/faker/src/autoload.php';

class VocalloSeed extends AbstractTestComponent
{
    public function run()
    {
        $blueDot = $this->blueDot;

        $faker = \Faker\Factory::create();

        $connection = new Connection();
        $connection
            ->setDatabaseName('langland')
            ->setHost('127.0.0.1')
            ->setPassword('root')
            ->setUser('root');

        $connection->connect();

        $blueDot->setConnection($connection);

        $languages = array(
            'croatian',
            'english',
            'french',
            'spanish',
            'german',
            'italian',
        );

        $languageModels = array();

        foreach ($languages as $language) {
            $languageModels[] = (new Language())->setLanguage($language);
        }

        $categories = array(
            'nature',
            'house',
            'road',
            'city',
            'construction',
            'programming',
            'medicine',
            'history',
            'hardware',
            'software',
        );

        $this->blueDot->execute('scenario.create_sentence', array(
            'create_sentence' => array('sentence' => 'Some sentence'),
            'create_lesson_sentence' => array(
                'internal_name' => 'internal name',
                'lesson_id' => 5,
            ),
            'create_lesson_sentence_translations' => array(
                'translation' => $faker->words(rand(1, 25)),
            ),
        ));

        $inserts = 0;
        $start = time();
        foreach ($languageModels as $languageModel) {
            $languageId = $blueDot->execute('simple.insert.create_language', $languageModel)
                ->success(function(PromiseInterface $promise) {
                    return $promise->getResult()->get('last_insert_id');
                })->getResult();

            $inserts++;

            $blueDot
                ->createStatementBuilder()
                ->addSql(sprintf('INSERT INTO courses (language_id, name) VALUES (%d, "%s")', $languageId, sprintf('%s course', ucfirst($languageModel->getLanguage()))))
                ->execute()
                ->success(function(PromiseInterface $promise) use ($blueDot, $languageModel) {
                    $courseId = $promise->getResult()->get('last_insert_id');

                    $blueDot->execute('simple.insert.create_class', array(
                        'course_id' => $courseId,
                        'name' => sprintf('Gentle %s introduction', ucfirst($languageModel->getLanguage())),
                    ));
                });

            $inserts++;

            for ($a = 0; $a < 10; $a++) {
                $category = new Category();
                $category->setCategory($categories[$a]);
                $category->setLanguageId($languageId);

                $categoryId = $blueDot->execute('simple.insert.create_category', $category)
                    ->success(function(PromiseInterface $promise) {
                        return $promise->getResult()->get('last_insert_id');
                    })->getResult();

                $inserts++;

                for ($i = 0; $i < 10; $i++) {
                    $blueDot->execute('scenario.insert_word', array(
                        'insert_word' => array(
                            'language_id' => $languageId,
                            'word' => $faker->word,
                            'type' => $faker->company,
                        ),
                        'insert_word_image' => array(
                            'relative_path' => 'relative_path',
                            'absolute_path' => 'absolute_path',
                            'file_name' => 'file_name',
                            'absolute_full_path' => 'absolute_full_path',
                            'relative_full_path' => 'relative_full_path',
                        ),
                        'insert_translation' => array(
                            'translation' => $faker->words(rand(1, 25)),
                        ),
                        'insert_word_category' => array(
                            'category_id' => $categoryId,
                        ),
                    ))->success(function(PromiseInterface $promise) use (&$inserts) {
                        $translationRowCount = $promise->getResult()->get('insert_translation')->get('row_count');
                        $insertWordRowCount = $promise->getResult()->get('insert_word')->get('row_count');
                        $insertCategoryRowCount = $promise->getResult()->get('insert_word_category')->get('row_count');

                        $inserts += (int) $translationRowCount;
                        $inserts += (int) $insertWordRowCount;
                        $inserts += (int) $insertCategoryRowCount;
                    });
                }
            }
        }

        $finish = time() - $start;

        $inserts++;

        $blueDot->execute('simple.update.update_working_language', array(
            'working_language' => 1,
            'id' => 1,
        ));
    }
}