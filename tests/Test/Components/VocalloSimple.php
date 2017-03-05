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
        $language = new Language();
        $language->setLanguage('croatian');

        $this->blueDot->execute('simple.select.find_language', $language)
            ->success(function(PromiseInterface $promise) {
                $language = $promise->getResult()->normalizeIfOneExists()->get('language');

                $this->phpunit->assertEquals('croatian', $language, 'simple.select.find_language has to return croatian');
            });
    }
}