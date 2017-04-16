<?php
/**
 * Created by PhpStorm.
 * User: mario
 * Date: 16.04.17.
 * Time: 12:58
 */

namespace Test\Components;

use BlueDot\BlueDotInterface;
use BlueDot\Entity\PromiseInterface;

class VocalloPreparedExecution implements TestComponentInterface
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
        $this->blueDot
            ->prepareExecution('simple.select.find_language', array(
            'language' => 'croatian',
            ))
            ->prepareExecution('simple.select.find_all_languages')
            ->prepareExecution('simple.insert.create_language', array(
                'language' => 'bulgarian'
            ))
            ->prepareExecution('simple.select.find_language', array(
                'language' => 'bulgarian',
            ))
            ->prepareExecution('simple.select.find_all_categories', array(
                'language_id' => 1,
            ));

        $promises = $this->blueDot->executePrepared();

        $this->phpunit->assertNotEmpty(
            $promises,
            sprintf('Promises should not be empty in %s', VocalloPreparedExecution::class)
        );

        foreach ($promises as $promise) {
            $this->phpunit->assertInstanceOf(
                PromiseInterface::class,
                $promise,
                sprintf(
                    '$promises array should be an array of %s objects in %s',
                    PromiseInterface::class,
                    VocalloPreparedExecution::class
                )
            );

            $this->phpunit->assertTrue(
                $promise->isSuccess(),
                sprintf(
                    'Query %s failed in %s',
                    $promise->getName(),
                    VocalloPreparedExecution::class
                )
            );
        }
    }
}