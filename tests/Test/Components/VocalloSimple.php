<?php

namespace Test\Components;

use BlueDot\BlueDotInterface;
use BlueDot\Entity\PromiseInterface;
use Test\Model\Language;

class VocalloSimple implements TestComponentInterface
{
    /**
     * @var \PHPUnit_Framework_Assert $phpunit
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

        $languages = array('nigerian', 'moltovian', 'russian', 'chinese');
        $languageModels = array();

        foreach ($languages as $language) {
            $languageModels[] = (new Language())->setLanguage($language);
        }

        $this->blueDot->execute('simple.insert.create_language', $languageModels)
            ->success(function(PromiseInterface $promise) {
                $this->phpunit->assertEquals(4, count($promise->getResult()->get('inserted_ids')), 'There should be 4 inserts for simple.insert.create_language');
                $this->phpunit->assertInternalType('int', (int) $promise->getResult()->get('last_insert_id'));
            });

        $this->blueDot->execute('simple.select.find_language', array(
            'language' => 'croatian',
        ))
            ->success(function(PromiseInterface $promise) {
                $language = $promise->getResult()->normalizeIfOneExists()->get('language');

                $this->phpunit->assertEquals('croatian', $language, 'simple.select.find_language has to return croatian');
            });

        $croatianLanguage = new Language();
        $croatianLanguage->setLanguage('croatian');

        $this->blueDot->execute('simple.select.find_language', $croatianLanguage)
            ->success(function(PromiseInterface $promise) {
                $language = $promise->getResult()->normalizeIfOneExists()->get('language');

                $this->phpunit->assertEquals('croatian', $language, 'simple.select.find_language has to return croatian');
            });

        $this->blueDot->execute('simple.insert.create_language', array(
            array('language' => 'japanese'),
            array('language' => 'belgium'),
            array('language' => 'swedish'),
            array('language' => 'norwegian'),
            array('language' => 'bosnian'),
        ));

        $bosnianLanguage = new Language();
        $bosnianLanguage->setLanguage('bosnian');

        $this->blueDot->execute('simple.select.find_language', $bosnianLanguage)
            ->success(function(PromiseInterface $promise) {
                $result = $promise->getResult();

                foreach ($result as $language) {
                    $this->phpunit->assertEquals('bosnian', $language['language'], 'simple.select.find_language has to return bosnian');
                }
            });
    }
}