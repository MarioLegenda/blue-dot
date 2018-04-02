<?php

namespace BlueDot\Kernel\Result;

use BlueDot\Common\FlowProductInterface;

interface KernelResultInterface
{
    /**
     * @return array
     */
    public function getResult(): array;
    /**
     * @return FlowProductInterface
     */
    public function getConfiguration(): FlowProductInterface;
}