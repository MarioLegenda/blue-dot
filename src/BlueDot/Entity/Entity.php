<?php


namespace BlueDot\Entity;

class Entity extends FilterableBaseEntity implements EntityInterface
{
    /**
     * SimpleEntity constructor.
     * @param string $name
     * @param array $data
     */
    public function __construct(string $name, array $data)
    {
        parent::__construct($name, $data);
    }
    /**
     * @inheritDoc
     */
    public function getRowCount(): ?int
    {
        if (!isset($this->data['row_count'])) return null;

        return $this->data['row_count'];
    }
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->data;
    }
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->data['type'];
    }
}