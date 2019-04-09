<?php

namespace BlueDot\Entity;

class Promise implements PromiseInterface
{
    /**
     * @var null|string $name
     */
    private $name;
    /**
     * @var EntityInterface $entity
     */
    private $entity;
    /**
     * Promise constructor.
     * @param EntityInterface|object|null $entity
     * @param string|null $name
     */
    public function __construct($entity = null, string $name = null)
    {
        $this->entity = $entity;
        $this->name = $name;
    }
    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
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