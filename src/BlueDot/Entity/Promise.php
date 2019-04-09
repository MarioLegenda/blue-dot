<?php

namespace BlueDot\Entity;

class Promise implements PromiseInterface
{
    /**
     * @var EntityInterface $entity
     */
    private $entity;
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