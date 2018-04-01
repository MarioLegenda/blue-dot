<?php

namespace BlueDot\Configuration\Flow;

use BlueDot\Configuration\Flow\Service\ServiceConfiguration;

class ServiceFlow
{
    /**
     * @param string $resolvedServiceName
     * @param array $serviceConfiguration
     * @return ServiceConfiguration
     */
    public function create(
        string $resolvedServiceName,
        array $serviceConfiguration
    ): ServiceConfiguration {
        return new ServiceConfiguration(
            $resolvedServiceName,
            $serviceConfiguration['item']['class']
        );
    }
}