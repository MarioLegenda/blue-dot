<?php

namespace BlueDot\Entity;

interface PromiseInterface
{
    /**
     * @return mixed
     */
    public function getResult();
    /**
     * @param \Closure $callback
     * @return PromiseInterface
     */
    public function success(\Closure $callback) : PromiseInterface;
    /**
     * @param \Closure $callback
     * @return PromiseInterface
     */
    public function failure(\Closure $callback) : PromiseInterface;
}