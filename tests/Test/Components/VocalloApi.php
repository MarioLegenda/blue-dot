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
        $dirs = $this->blueDot->api()->getDirs();

        $validDirs = array(
            __DIR__.'/../config/api'
        );

        foreach ($dirs as $dir) {
            if (in_array($dir, $validDirs) === false) {
                $this->phpunit->fail(sprintf('Directory %s not found in BlueDot API interface but it should have been', $dir));
            }
        }

        $files = new \DirectoryIterator(__DIR__.'/../config/api');
        $validFiles = array('language.yml', 'user.yml', 'kreten.yml');

        foreach ($files as $file) {
            if ($file->getFilename() === '.' or $file->getFilename() === '..') {
                continue;
            }

            if (in_array($file->getFilename(), $validFiles) === false) {
                $this->phpunit->fail(sprintf('File %s not found in BlueDot API interface but it should have been', $file->getFilename()));
            }
        }

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