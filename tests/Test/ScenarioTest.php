<?php

namespace Test;

use BlueDot\BlueDot;
use BlueDot\Entity\Entity;

class ScenarioTest extends AbstractBlueDotTest
{
    public function testScenario()
    {
        $entity = $this->blueDot->execute('scenario.find_words', array(
            'find_working_language' => array(
                'user_id' => 1,
            ),
            'select_all_words' => array(
                'language_id' => 1,
            ),
        ))->getResult();

        $entity = new Entity($entity->get('select_all_words'));

    }
}