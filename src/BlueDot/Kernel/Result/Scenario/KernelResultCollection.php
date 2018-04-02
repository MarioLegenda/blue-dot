<?php

namespace BlueDot\Kernel\Result\Scenario;

use BlueDot\Common\ArrayNotationInterface;
use BlueDot\Result\NullQueryResult;

class KernelResultCollection implements ArrayNotationInterface, \IteratorAggregate, \Countable
{
    /**
     * @var array $results
     */
    private $results = [];
    /**
     * @param string $resolvedStatementName
     * @param $result
     */
    public function add(string $resolvedStatementName, $result)
    {
        if ($this->has($resolvedStatementName)) {
            $message = sprintf(
                'Scenario result statement collection already has a result from \'%s\'',
                $resolvedStatementName
            );

            throw new \RuntimeException($message);
        }

        $this->results[$resolvedStatementName] = $result;
    }
    /**
     * @param string $resolvedStatementName
     * @return bool
     */
    public function has(string $resolvedStatementName): bool
    {
        return array_key_exists($resolvedStatementName, $this->results);
    }
    /**
     * @param string $resolvedStatementName
     * @return array|object
     */
    public function get(string $resolvedStatementName)
    {
        if (!$this->has($resolvedStatementName)) {
            return [];
        }

        return $this->results[$resolvedStatementName];
    }
    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->results;
    }
    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->results);
    }
    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->results);
    }
}