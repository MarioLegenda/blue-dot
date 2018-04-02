<?php

namespace BlueDot\Kernel\Result\Scenario;

use BlueDot\Common\FlowProductInterface;
use BlueDot\Configuration\Flow\Scenario\ScenarioConfiguration;
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
     * @param ScenarioConfiguration $configuration
     * @param array $result
     */
    public function __construct(
        ScenarioConfiguration $configuration,
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