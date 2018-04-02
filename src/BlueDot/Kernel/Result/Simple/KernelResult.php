<?php

namespace BlueDot\Kernel\Result\Simple;

use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Flow\Simple\SimpleConfiguration;
use BlueDot\Kernel\Result\KernelResultInterface;

class KernelResult implements KernelResultInterface
{
    /**
     * @var SimpleConfiguration $configuration
     */
    private $configuration;
    /**
     * @var array $result
     */
    private $result;
    /**
     * KernelResult constructor.
     * @param SimpleConfiguration $configuration
     * @param array $result
     */
    public function __construct(
        SimpleConfiguration $configuration,
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