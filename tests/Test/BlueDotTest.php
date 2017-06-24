<?php

namespace Test;

use BlueDot\Entity\PromiseInterface;

class BlueDotTest extends AbstractBlueDotTest
{
    public function testSimpleStatements()
    {
        Seed::instance()->seed();
    }

    public function testPreparedExecutions()
    {
        Seed::instance()->seed();

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

        $this->assertNotEmpty(
            $promises,
            sprintf('Promises should not be empty')
        );

        foreach ($promises as $promise) {
            $this->assertInstanceOf(
                PromiseInterface::class,
                $promise,
                sprintf(
                    '$promises array should be an array of PromiseInterface objects'
                )
            );

            $this->assertTrue(
                $promise->isSuccess(),
                sprintf(
                    'Query %s failed',
                    $promise->getName()
                )
            );
        }
    }
}