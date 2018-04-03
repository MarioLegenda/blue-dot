<?php

namespace BlueDot\Entity;

class Promise implements PromiseInterface
{
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var Entity|object|null $entity
     */
    private $entity;
    /**
     * @var bool $callbackCalled
     */
    private $callbackCalled = false;
    /**
     * @var mixed $result
     */
    private $result = null;
    /**
     * Promise constructor.
     * @param Entity|object|null $entity
     * @param string|null $name
     */
    public function __construct($entity = null, string $name = null)
    {
        $this->entity = $entity;
        $this->name = $name;
    }
    /**
     * @inheritdoc
     */
    public function getResult()
    {
        if ($this->callbackCalled === true) {
            return $this->result;
        }

        if ($this->entity instanceof Entity) {
            return ($this->entity->isEmpty()) ? null : $this->entity;
        }

        return $this->entity;
    }
    /**
     * @inheritdoc
     */
    public function getOriginalEntity()
    {
        return $this->entity;
    }
    /**
     * @param \Closure $callback
     * @return PromiseInterface
     */
    public function success(\Closure $callback) : PromiseInterface
    {
        if ($this->callbackCalled === true) {
            return $this;
        }

        if (is_null($this->getResult())) {
            return $this;
        }

        $this->result = $callback->__invoke($this);

        $this->callbackCalled = true;

        return $this;
    }
    /**
     * @param \Closure $callback
     * @return PromiseInterface
     */
    public function failure(\Closure $callback) : PromiseInterface
    {
        if ($this->callbackCalled === true) {
            return $this;
        }

        if (is_null($this->getResult())) {
            $this->result = $callback->__invoke($this);

            $this->callbackCalled = true;
        }

        return $this;
    }
    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @return bool
     */
    public function isSuccess() : bool
    {
        return !is_null($this->getResult());
    }
    /**
     * @return bool
     */
    public function isFailure() : bool
    {
        return is_null($this->getResult());
    }
}