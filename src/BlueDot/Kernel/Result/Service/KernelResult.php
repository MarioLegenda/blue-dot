<?php

namespace BlueDot\Kernel\Result\Service;

use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Flow\Service\ServiceConfiguration;
use BlueDot\Kernel\Result\KernelResultInterface;

class KernelResult implements KernelResultInterface
{
    /**
     * @var ServiceConfiguration $configuration
     */
    private $configuration;
    /**
     * @var array $result
     */
    private $result;
    /**
     * KernelResult constructor.
     * @param ServiceConfiguration $configuration
     * @param array $result
     */
    public function __construct(
        ServiceConfiguration $configuration,
        array $result
    ) {
        $this->configuration = $configuration;
        $this->result = $result;
    }
    /**
     * @return FlowProductInterface
     */
    public function getConfiguration(): FlowProductInterface
    {
        return $this->configuration;
    }
    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }
}