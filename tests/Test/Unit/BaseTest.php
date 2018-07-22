<?php

namespace Test\Unit;

use BlueDot\BlueDot;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    public function setUp()
    {
        $preparedExecutionConfig = __DIR__ . '/../config/result/prepared_execution_test.yml';

        $blueDot = new BlueDot($preparedExecutionConfig);

        $promise = $blueDot->execute('scenario.table_creation');

        parent::setUp();
    }
}