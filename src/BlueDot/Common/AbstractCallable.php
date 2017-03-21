<?php

namespace BlueDot\Common;

use BlueDot\BlueDotInterface;

abstract class AbstractCallable implements CallableInterface
{
    /**
     * @var BlueDotInterface $blueDot
     */
    protected $blueDot;
    /**
     * @var array $parameters
     */
    protected $parameters;
    /**
     * LastWordsCallable constructor.
     * @param BlueDotInterface $blueDot
     * @param array $parameters
     */
    public function __construct(BlueDotInterface $blueDot, array $parameters = null)
    {
        $this->blueDot = $blueDot;
        $this->parameters = $parameters;
    }
}