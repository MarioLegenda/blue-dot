<?php

namespace BlueDot\Common\Enum;

use BlueDot\Common\ArrayNotationInterface;

class BaseType implements TypeInterface, ArrayNotationInterface
{
    /**
     * @var array $allTypes
     */
    protected $allTypes;
    /**
     * @var array $type
     */
    protected $type;
    /**
     * BaseType constructor.
     * @param array $type
     * @param array $types
     */
    public function __construct(array $type, array $types)
    {
        $this->type = $type;
        $this->allTypes = $types;
    }
    /**
     * @param mixed $value
     * @return TypeInterface
     */
    public static function fromValue($value): TypeInterface
    {
        foreach (static::$types as $key => $type) {
            if ($value === $type) {
                return new static([$key => $type], static::$types);
            }
        }

        throw new \RuntimeException(sprintf('%s could not be created from value %s', static::class, (string) $value));
    }
    /**
     * @param $key
     * @return static
     */
    public static function fromKey($key): TypeInterface
    {
        foreach (static::$types as $k => $type) {
            if ($k === $key) {
                return new static([$k => $type], static::$types);
            }
        }

        throw new \RuntimeException(sprintf('%s could not be created from key', static::class));
    }
    /**
     * @param $key
     * @return bool
     */
    public function isTypeByKey($key): bool
    {
        return array_key_exists($key, $this->type);
    }
    /**
     * @param $value
     * @return bool
     */
    public function isTypeByValue($value): bool
    {
        $v = array_values($this->type)[0];

        return $v === $value;
    }
    /**
     * @return mixed
     */
    public function getKey()
    {
        return array_keys($this->type)[0];
    }
    /**
     * @return mixed
     */
    public function getValue()
    {
        return array_values($this->type)[0];
    }
    /**
     * @param TypeInterface $type
     * @return bool
     */
    public function equals(TypeInterface $type): bool
    {
        return $this->equalsKey($type->getKey()) && $this->equalsValue($type->getValue());
    }
    /**
     * @param $value
     * @return bool
     */
    public function equalsValue($value): bool
    {
        return $value === $this->getValue();
    }
    /**
     * @param $key
     * @return bool
     */
    public function equalsKey($key): bool
    {
        return $key === $this->getKey();
    }
    /**
     * @param array $range
     * @return bool
     */
    public function inValueRange(array $range): bool
    {
        foreach ($range as $option) {
            if ($option === $this->getValue()) {
                return true;
            }
        }

        return false;
    }
    /**
     * @param array $range
     * @return bool
     */
    public function inKeyRange(array $range): bool
    {
        foreach ($range as $option) {
            if ($option === $this->getKey()) {
                return true;
            }
        }

        return false;
    }
    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->allTypes;
    }
    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getValue();
    }
}
