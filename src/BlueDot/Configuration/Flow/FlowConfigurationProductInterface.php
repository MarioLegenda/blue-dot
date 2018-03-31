<?php

namespace BlueDot\Configuration\Flow;

use BlueDot\Common\FlowProductInterface;

interface FlowConfigurationProductInterface extends FlowProductInterface
{
    /**
     * @param array|null $userParameters
     */
    public function injectUserParameters($userParameters);
}