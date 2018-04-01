<?php

namespace BlueDot\Kernel\Connection;

class Attributes implements \IteratorAggregate
{
    /**
     * @var array $attributes
     */
    private $attributes;
    /**
     * Attributes constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }
    /**
     * @param string $attribute
     * @param $value
     */
    public function addAttribute(string $attribute, $value)
    {
        if (!$this->hasAttribute($attribute)) {
            $this->attributes[$attribute] = $value;
        }

        throw new \RuntimeException(sprintf('Attribute \'%s\' already exists', $attribute));
    }
    /**
     * @param string $attribute
     * @return bool
     */
    public function hasAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->attributes);
    }
    /**
     * @param string $attribute
     * @return null|string
     */
    public function getAttribute(string $attribute): ?string
    {
        if ($this->hasAttribute($attribute)) {
            return $this->attributes[$attribute];
        }

        return null;
    }
    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->attributes);
    }
    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}