<?php

namespace Test;

use BlueDot\BlueDot;

class CompanyDatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testCompanyDatabase()
    {
        $blueDot = new BlueDot(__DIR__.'/config/company_db_config.yml');

        $blueDot->execute('scenario.create_database');
    }
}