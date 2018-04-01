<?php

namespace BlueDot\Common\Enum;

interface TypeInterface
{
    /**
     * @param $value
     * @return TypeInterface
     * @throw \RuntimeException
     */
    public static function fromValue($value): TypeInterface;
    /**
     * @param $value
     * @return TypeInterface
     * @throw \RuntimeException
     */
    public static function fromKey($value): TypeInterface;
    /**
     * @param $key
     * @return bool
     */
    public function isTypeByKey($key): bool;
    /**
     * @param $value
     * @return bool
     */
    public function isTypeByValue($value): bool;
    /**
     * @return mixed
     */
    public function getKey();
    /**
     * @return mixed
     */
    public function getValue();
    /**
     * @param TypeInterface $type
     * @return bool
     */
    public function equals(TypeInterface $type): bool;
    /**
     * @param $value
     * @return bool
     */
    public function equalsValue($value): bool;
    /**
     * @param $key
     * @return bool
     */
    public function equalsKey($key): bool;
    /**
     * @param array $range
     * @return bool
     */
    public function inValueRange(array $range): bool;
    /**
     * @param array $range
     * @return bool
     */
    public function inKeyRange(array $range): bool;
}