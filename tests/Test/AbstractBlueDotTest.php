<?php

namespace Test;

use BlueDot\Database\Connection;
use BlueDot\Entity\PromiseInterface;
use BlueDot\BlueDot;
use Test\Model\Language;
use Test\Model\Category;

class AbstractBlueDotTest extends \PHPUnit_Framework_TestCase
{
    protected $blueDot;

    public function setUp()
    {
        $blueDot = new BlueDot(__DIR__.'/config/vocallo_user_db.yml');

        $connection = new Connection();

        $connection
            ->setUser('root')
            ->setPassword('root')
            ->setHost('127.0.0.1')
            ->setDatabaseName('');

        $blueDot
            ->createStatementBuilder($connection)
            ->addSql('DROP DATABASE IF EXISTS langland')
            ->execute();

        $blueDot
            ->createStatementBuilder($connection)
            ->addSql('CREATE DATABASE IF NOT EXISTS langland CHARACTER SET = \'utf8\' COLLATE = \'utf8_general_ci\'')
            ->execute();

        $blueDot->setConfiguration(__DIR__ . '/config/vocallo_user_db.yml');
        $connection
            ->close()
            ->setDatabaseName('langland')
            ->connect();

        $blueDot->setConnection($connection);

        $blueDot->execute('scenario.seed');

        $faker = \Faker\Factory::create();

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

        $this->blueDot = $blueDot;
    }
}