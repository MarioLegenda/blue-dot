<?php

namespace BlueDot\Configuration;

use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;

interface ConfigurationCompilerInterface
{
    /**
     * @return SimpleConfiguration|ScenarioConfiguration|ServiceConfiguration
     */
    public function compile();
}