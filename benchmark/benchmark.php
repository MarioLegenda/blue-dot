<?php

require __DIR__.'/../vendor/autoload.php';

use BlueDot\BlueDot;
use Faker\Factory;

$config = __DIR__.'/../tests/Test/config/result/prepared_execution_test.yml';
$faker = Factory::create();
$blueDot = new BlueDot($config);

$blueDot->getConnection()->connect();

$blueDot->getConnection()->getPDO()->exec('TRUNCATE TABLE user');
$blueDot->getConnection()->getPDO()->exec('TRUNCATE TABLE addresses');

dump("Start memory usage: ".(memory_get_usage() / 1000 / 1000)." MB");

$start = time();
for ($i = 0; $i < 100000; $i++) {

    if (($i % 1000) === 0 and $i != 0) {
        $memory = (memory_get_usage() / 1000 / 1000)." MB";
        dump(sprintf('Processed %d records. Memory usage: %s', $i, $memory));
    }

    $blueDot->prepareExecution('scenario.insert_user', [
        'insert_user' => [
            'username' => $faker->userName,
            'name' => $faker->name,
            'lastname' => $faker->lastName,
        ],
        'insert_address' => [
            'address' => $faker->address,
        ],
    ]);
}

$blueDot->executePrepared();

$end = time();

dump("Total time in seconds: ".($end - $start));

dump("End memory usage: ".(memory_get_usage() / 1000 / 1000)." MB");
dump("Peak memory usage: ".(memory_get_peak_usage() / 1000 / 1000)." MB");

$start = time();
for ($i = 0; $i < 100000; $i++) {

    if (($i % 1000) === 0 and $i != 0) {
        $memory = (memory_get_usage() / 1000 / 1000)." MB";
        dump(sprintf('Processed %d records. Memory usage: %s', $i, $memory));
    }

    $blueDot->prepareExecution('scenario.insert_user', [
        'insert_user' => [
            'username' => $faker->userName,
            'name' => $faker->name,
            'lastname' => $faker->lastName,
        ],
        'insert_address' => [
            'address' => $faker->address,
        ],
    ]);
}

$blueDot->executePrepared();

$end = time();

dump("Total time in seconds: ".($end - $start));

dump("End memory usage: ".(memory_get_usage() / 1000 / 1000)." MB");
dump("Peak memory usage: ".(memory_get_peak_usage() / 1000 / 1000)." MB");

$blueDot->getConnection()->getPDO()->exec('TRUNCATE TABLE user');
$blueDot->getConnection()->getPDO()->exec('TRUNCATE TABLE addresses');