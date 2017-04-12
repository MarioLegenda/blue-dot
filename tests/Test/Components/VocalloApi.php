<?php

namespace Test\Components;

use BlueDot\BlueDotInterface;
use BlueDot\Entity\PromiseInterface;

class VocalloApi implements TestComponentInterface
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

        $this->blueDot->api()->putAPI(__DIR__.'/../config/api');
    }

    public function run()
    {
        $this->blueDot
            ->useApi('language')
            ->execute('simple.select.find_all_languages')
            ->success(function(PromiseInterface $promise) {
            })
            ->failure(function() {
                $this->phpunit->fail(VocalloApi::class.' failed for statement simple.select.find_all_languages');
            })
            ->getResult();

        $this->blueDot->api()->putAPI(__DIR__.'/../config/category.yml');

        $this->blueDot
            ->useApi('category')
            ->execute('simple.select.find_all_categories', array(
                'language_id' => 1,
            ))
            ->success(function(PromiseInterface $promise) {

            })
            ->failure(function() {
                $this->phpunit->fail(VocalloApi::class.' failed for statement simple.select.find_all_categories');
            });
    }
}