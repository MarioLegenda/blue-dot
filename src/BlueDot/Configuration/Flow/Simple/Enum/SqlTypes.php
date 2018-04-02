<?php

namespace BlueDot\Configuration\Flow\Simple\Enum;

use BlueDot\Common\ArrayNotationInterface;

class SqlTypes implements ArrayNotationInterface, \IteratorAggregate
{
    /**
     * @var SqlTypes $instance
     */
    private static $instance;
    /**
     * @var array $types
     */
    private $types = [
        'select' => SelectSqlType::class,
        'update' => UpdateSqlType::class,
        'insert' => InsertSqlType::class,
        'delete' => DeleteSqlType::class,
    ];
    /**
     * @return SqlTypes
     */
    public static function instance(): SqlTypes
    {
        static::$instance = (static::$instance instanceof static) ? static::$instance : new static();

        return static::$instance;
    }

    private function __construct(){}

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->types;
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->types);
    }
}