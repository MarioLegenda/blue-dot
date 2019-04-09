<?php

namespace BlueDot\Entity;

class Promise implements PromiseInterface
{
    /**
     * @var BaseEntity|object|null $entity
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
     * @param EntityInterface|object|null $entity
     */
    public function __construct($entity = null)
    {
        $this->entity = $entity;
    }
    /**
     * @inheritdoc
     */
    public function getEntity(): EntityInterface
    {
        if ($this->callbackCalled === true) {
            return $this->result;
        }

        return $this->entity;
    }
    /**
     * @inheritdoc
     */
    public function getArrayResult(): ?array
    {
        $entity = $this->getEntity();

        if ($entity instanceof EntityInterface) {
            return $entity->toArray();
        }

        return null;
    }
    /**
     * @param \Closure $closure
     */
    public function onResultReady(\Closure $closure): void
    {
        $closure->__invoke($this->getEntity());
    }
}