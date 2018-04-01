<?php

namespace BlueDot\Kernel\Strategy;

use BlueDot\Common\Enum\TypeInterface;
use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Kernel\Strategy\Enum\ScenarioStrategyType;
use BlueDot\Kernel\Strategy\Enum\SimpleStrategyType;

class StrategyTypeFactory
{
    /**
     * @param FlowProductInterface $configuration
     * @return TypeInterface
     */
    public static function getType(FlowProductInterface $configuration): TypeInterface
    {
        if ($configuration instanceof SimpleConfiguration) {
            return SimpleStrategyType::fromValue();
        }

        if ($configuration instanceof ScenarioConfiguration) {
            return ScenarioStrategyType::fromValue();
        }
    }
}