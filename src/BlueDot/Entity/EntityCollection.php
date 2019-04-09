<?php


namespace BlueDot\Entity;


class EntityCollection implements EntityInterface
{
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var EntityInterface[] $entities
     */
    private $entities = [];

    public function __construct(string $name, array $entities)
    {
        $this->name = $name;
        $this->entities = $entities;
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @param string $name
     * @return EntityInterface
     */
    public function getEntity(string $name): ?EntityInterface
    {
        if ($this->hasEntity($name)) {
            return $this->entities[$name];
        }

        throw new \InvalidArgumentException("Entity with name $name does not exist");
    }
    /**
     * @param string $name
     * @return bool
     */
    public function hasEntity(string $name): bool
    {
        return array_key_exists($name, $this->entities);
    }
    /**
     * @return string
     *
     * Hardcoded because EntityCollection will only be used in scenarios
     */
    public function getType(): string
    {
        return 'scenario';
    }
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->entities;
    }
}