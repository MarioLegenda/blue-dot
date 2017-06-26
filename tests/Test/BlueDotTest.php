<?php

namespace Test;

use BlueDot\BlueDot;
use BlueDot\Common\ArgumentBag;
use BlueDot\Entity\Entity;
use Test\Model\User;

class BlueDotTest extends AbstractBlueDotTest
{
    public function testSimpleStatements()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/test_simple_db.yml');

        Seed::instance()->reset($blueDot);

        $promise = $blueDot->execute('simple.insert.create_user', array(
            'name' => 'Mile',
            'lastname' => 'Mile',
            'username' => 'Mile'
        ));

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        $this->assertInstanceOf(Entity::class, $result);
        $this->assertEquals(1, $result->get('last_insert_id'));

        $insertedIds = $result->get('inserted_ids');

        $this->assertInstanceOf(ArgumentBag::class, $insertedIds);
        $this->assertNotEmpty($insertedIds->toArray());
        $this->assertEquals(1, $insertedIds[0]);

        $user = new User();
        $user->setName('Mile');
        $user->setLastname('Mile');
        $user->setUsername('Mile');

        $promise = $blueDot->execute('simple.insert.create_user', $user);

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        $this->assertInstanceOf(Entity::class, $result);
        $this->assertEquals(2, (int)$result->get('last_insert_id'));

        $insertedIds = $result->get('inserted_ids');

        $this->assertInstanceOf(ArgumentBag::class, $insertedIds);
        $this->assertNotEmpty($insertedIds->toArray());

        $users = array(
            array(
                'name' => 'Mile',
                'lastname' => 'Mile',
                'username' => 'Mile'
            ),
            array(
                'name' => 'Mirko',
                'lastname' => 'Mirko',
                'username' => 'Mirko',
            ),
            array(
                'name' => 'Miroslav',
                'lastname' => 'Miroslav',
                'username' => 'Miroslav',
            ),
        );

        $promise = $blueDot->execute('simple.insert.create_user', $users);

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        $this->assertInstanceOf(Entity::class, $result);
        $this->assertEquals(5, $result->get('last_insert_id'));

        $insertedIds = $result->get('inserted_ids');

        $this->assertInstanceOf(ArgumentBag::class, $insertedIds);
        $this->assertNotEmpty($insertedIds->toArray());
        $this->assertEquals(3, count($insertedIds->toArray()));
        $this->assertEquals(3, $insertedIds[0]);

        foreach ($insertedIds as $id) {
            $this->assertInternalType('integer', (int)$id);
        }

        $userModels = array();

        foreach ($users as $userArr) {
            $user = new User();
            $user->setName($userArr['name']);
            $user->setLastname($userArr['lastname']);
            $user->setUsername($userArr['username']);

            $userModels[] = $user;
        }

        $promise = $blueDot->execute('simple.insert.create_user', $userModels);

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        $this->assertInstanceOf(Entity::class, $result);
        $this->assertEquals(8, (int)$result->get('last_insert_id'));

        $insertedIds = $result->get('inserted_ids');

        $this->assertInstanceOf(ArgumentBag::class, $insertedIds);
        $this->assertNotEmpty($insertedIds->toArray());
        $this->assertEquals(3, count($insertedIds->toArray()));

        foreach ($insertedIds as $id) {
            $this->assertInternalType('integer', (int)$id);
        }

        $promise = $blueDot->execute('simple.select.find_user', array(
            'user_id' => 8,
        ));

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult()->normalizeIfOneExists();

        $this->assertInstanceOf(Entity::class, $result);

        $this->assertEquals(8, $result->get('id'));
        $this->assertEquals('Miroslav', $result->get('name'));
        $this->assertEquals('Miroslav', $result->get('lastname'));
        $this->assertEquals('Miroslav', $result->get('username'));

        $promise = $blueDot->execute('simple.select.find_user_by_model', array(
            'user_id' => 8,
        ));

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        $this->assertInstanceOf(User::class, $result);

        $this->assertEquals('Miroslav', $result->getName());
        $this->assertEquals('Miroslav', $result->getLastname());
        $this->assertEquals('Miroslav', $result->getUsername());
    }

    public function testScenarioStatements()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/test_scenario_db.yml');

        Seed::instance()->reset($blueDot);

        $promise = $blueDot->execute('scenario.create_user_if_exists', array(
            'find_user' => array(
                'user_id' => 1
            ),
        ));

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        $this->assertInstanceOf(Entity::class, $result);
        $this->assertNull($result->get('find_user')->get('row_count'));
        $this->assertFalse($result->has('create_user'));

        $blueDot->execute('simple.insert.create_user', array(
            'name' => 'Mile',
            'lastname' => 'Mile',
            'username' => 'Mile'
        ));

        $promise = $blueDot->execute('scenario.create_user_if_exists', array(
            'find_user' => array(
                'user_id' => 1
            ),
        ));

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        $this->assertInstanceOf(Entity::class, $result);

        $this->assertEquals(1, count($result->get('find_user')));
        $this->assertTrue($result->has('create_user'));
        $this->assertEquals(2, $result->get('create_user')->get('last_insert_id'));
        $this->assertEquals(1, $result->get('create_user')->get('row_count'));

        $promise = $blueDot->execute('scenario.create_user_if_not_exists', array(
            'find_user' => array(
                'user_id' => 10
            ),
            'create_user' => array(
                'name' => 'Zdravko',
                'lastname' => 'Zdravko',
                'username' => 'Zdravko',
            ),
        ));

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        $this->assertInstanceOf(Entity::class, $result);

        $this->assertNull($result->get('find_user')->get('row_count'));
        $this->assertEquals(3, $result->get('create_user')->get('last_insert_id'));
        $this->assertEquals(1, $result->get('create_user')->get('row_count'));

        $promise = $blueDot->execute('scenario.create_word', array(
            'find_user' => array(
                'user_id' => 1,
            ),
            'create_language' => array(
                'language' => 'Language',
            ),
            'create_word' => array(
                'word' => 'Some word',
                'type' => 'Some type',
            ),
        ));

        $this->assertTrue($promise->isSuccess());

        $result = $promise->getResult();

        $this->assertInstanceOf(Entity::class, $result);

        $user = $result->get('find_user')->normalizeIfOneExists();
        $language = $result->get('create_language');
        $word = $result->get('create_word');

        $this->assertEquals('1', $user->get('id'));
        $this->assertEquals('Mile', $user->get('name'));
        $this->assertEquals('Mile', $user->get('lastname'));
        $this->assertEquals('Mile', $user->get('username'));

        $this->assertEquals(1, $language->get('last_insert_id'));
        $this->assertEquals(1, $language->get('row_count'));

        $this->assertEquals(1, $word->get('last_insert_id'));
        $this->assertEquals(1, $word->get('row_count'));
    }

    public function testApi()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/test_scenario_db.yml');

        Seed::instance()->reset($blueDot);

        $blueDot->api()->putAPI(__DIR__.'/config/api');

        $api = $blueDot->api();

        $this->assertSame($api->getCurrentlyUsingAPI(), 'test_scenario_db');

        $api->useAPI('language');

        $this->assertSame($api->getCurrentlyUsingAPI(), 'language');

        $api->useAPI('user');

        $this->assertSame($api->getCurrentlyUsingAPI(), 'user');
    }

    public function testEntityMethods()
    {
        $blueDot = new BlueDot(__DIR__ . '/config/test_scenario_db.yml');

        Seed::instance()->reset($blueDot);

        $translations = array();
        for ($i = 0; $i < 50; $i++) {
            $translations[] = md5(rand(1, 9999));
        }

        $blueDot->execute('simple.insert.create_user', array(
            'name' => 'Mile',
            'lastname' => 'Mile',
            'username' => 'Mile'
        ));

        $promise = $blueDot->execute('scenario.create_word_translation', array(
            'find_user' => array(
                'user_id' => 1,
            ),
            'create_language' => array(
                'language' => 'Language',
            ),
            'create_word' => array(
                'word' => 'Some word',
                'type' => 'Some type',
            ),
            'create_translation' => array(
                'translation' => $translations,
            ),
        ));

        $wordId = $promise->getResult()->get('create_word')->get('last_insert_id');

        $promise = $blueDot->execute('simple.select.find_word', array(
            'word_id' => $wordId,
        ));

        $result = $promise->getResult();

        $findBy = $result->findBy('id', 1);

        $this->assertEquals(50, count($findBy));

        $extractColumn = $result->extractColumn('id', 'ID');

        $this->assertEquals('ID', array_keys($extractColumn)[0]);
        $this->assertEquals(50, count($extractColumn['ID']));

        $normalized = $result->normalizeJoinedResult(array(
            'linking_column' => 'id',
            'columns' => array('translation'),
        ))[0];

        $translations = $normalized['translation'];

        $this->assertEquals(50, count($translations));
    }
}