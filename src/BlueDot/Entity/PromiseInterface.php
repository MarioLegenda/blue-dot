<?php

namespace BlueDot\Entity;

interface PromiseInterface
{
    /**
     * @return Entity|array|null
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
    /**
     * @return string|null
     */
    public function getName();
    /**
     * @return Entity|null
     */
    public function getOriginalEntity();
    /**
     * @return bool
     */
    public function isSuccess() : bool;
    /**
     * @return bool
     */
    public function isFailure() : bool;
}