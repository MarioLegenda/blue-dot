<?php

namespace Test;

use BlueDot\BlueDot;
use BlueDot\Database\Connection;
use Test\Model\Language;
use BlueDot\Entity\PromiseInterface;
use Test\Model\Category;

class Seed
{
    /**
     * @var Seed $instance
     */
    private static $instance;
    /**
     * @var bool $hasSeeded
     */
    private $hasSeeded = false;
    /**
     * @return Seed
     */
    public static function instance()
    {
        self::$instance = (self::$instance instanceof self) ? self::$instance : new self();

        return self::$instance;
    }

    public function seed()
    {
        $blueDot = new BlueDot(__DIR__.'/config/vocallo_user_db.yml');

        $faker = \Faker\Factory::create();

        $connection = new Connection();
        $connection
            ->setDatabaseName('langland')
            ->setHost('127.0.0.1')
            ->setPassword('root')
            ->setUser('root');

        $connection->connect();

        $blueDot
            ->setConnection($connection)
            ->useApi('vocallo_user_db');

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

        foreach ($languageModels as $languageModel) {
            $languageId = $blueDot->execute('simple.insert.create_language', $languageModel)
                ->success(function(PromiseInterface $promise) {
                    return $promise->getResult()->get('last_insert_id');
                })->getResult();

            for ($a = 0; $a < 10; $a++) {
                $category = new Category();
                $category->setCategory($categories[$a]);
                $category->setLanguageId($languageId);

                $categoryId = $blueDot->execute('simple.insert.create_category', $category)
                    ->success(function(PromiseInterface $promise) {
                        return $promise->getResult()->get('last_insert_id');
                    })->getResult();

                for ($i = 0; $i < 10; $i++) {
                    $blueDot->execute('scenario.create_word', array(
                        'find_working_language' => array(
                            'user_id' => 1,
                        ),
                        'create_word' => array(
                            'word' => $faker->word,
                            'type' => $faker->company,
                        ),
                        'create_image' => array(
                            'relative_path' => 'relative_path',
                            'absolute_path' => 'absolute_path',
                            'file_name' => 'file_name',
                            'absolute_full_path' => 'absolute_full_path',
                            'relative_full_path' => 'relative_full_path',
                        ),
                        'create_word_categories' => array(
                            'category_id' => $categoryId,
                        ),
                        'create_translations' => array(
                            'translation' => $faker->words(rand(1, 25)),
                        ),
                    ));
                }
            }
        }
    }

}