<?php

namespace BlueDot\Entity;

interface PromiseInterface
{
    /**
     * @return EntityInterface|array|null
     */
    public function getArrayResult(): ?array;
    /**
     * @return EntityInterface
     */
    public function getEntity(): EntityInterface;
    /**
     * @param \Closure $closure
     * @return mixed
     */
    public function onResultReady(\Closure $closure): void;
}