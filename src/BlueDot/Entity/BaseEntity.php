<?php

namespace BlueDot\Entity;

class BaseEntity
{
    /**
     * @var string
     */
    protected $data;
    /**
     * @var string
     */
    protected $name;
    /**
     * Entity constructor.
     * @param string $name
     * @param array $data
     */
    public function __construct(string $name, array $data)
    {
        $this->data = $data;
        $this->name = $name;
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return (isset($this->data['data'])) ? $this->data['data'] : null;
    }
    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        $rowCountEmpty = true;

        if (isset($this->data['row_count']) and $this->data['row_count'] !== 0) $rowCountEmpty = false;

        return empty($this->getData()) and $rowCountEmpty;
    }
}