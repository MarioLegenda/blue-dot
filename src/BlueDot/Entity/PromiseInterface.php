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
    /**
     * @param string $name
     * @return mixed
     */
    public function setName(string $name);
    /**
     * @return string|null
     */
    public function getName();
    /**
     * @return mixed
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