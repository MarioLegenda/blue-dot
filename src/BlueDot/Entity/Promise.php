<?php

namespace BlueDot\Entity;

class Promise implements PromiseInterface
{
    /**
     * @var Entity|object|null $entity
     */
    private $entity;
    /**
     * Promise constructor.
     * @param Entity|object|null $entity
     */
    public function __construct($entity = null)
    {
        $this->entity = $entity;
    }
    /**
     * @return Entity|object|null
     */
    public function getResult()
    {
        if ($this->entity instanceof Entity) {
            return ($this->entity->isEmpty()) ? null : $this->entity;
        }

        return $this->entity;
    }
    /**
     * @param \Closure $callback
     * @return PromiseInterface
     */
    public function success(\Closure $callback) : PromiseInterface
    {
        if (is_null($this->getResult())) {
            return $this;
        }

        $callback->__invoke($this);

        return $this;
    }
    /**
     * @param \Closure $callback
     * @return PromiseInterface
     */
    public function failure(\Closure $callback) : PromiseInterface
    {
        if (!is_null($this->getResult())) {
            $callback->__invoke($this);

            return $this;
        }

        return $this;
    }
}