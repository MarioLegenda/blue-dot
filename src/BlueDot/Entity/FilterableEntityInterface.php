<?php

namespace BlueDot\Entity;

use BlueDot\Exception\EntityException;

interface FilterableEntityInterface
{
    /**
     * @param string $column
     * @param mixed $value
     * @return FilterableEntityInterface
     * @throws EntityException
     *
     * Finds multiple results by column and value and returns a an array
     */
    public function find(string $column, $value): FilterableEntityInterface;
    /**
     * @param string $column
     * @param $value
     * @throws EntityException
     * @return FilterableEntityInterface
     *
     * Finds a single result from an array of result by its value and returns a new Entity
     */
    public function findBy(string $column, $value): FilterableEntityInterface;
    /**
     * @param string $column
     * @param string|null $alias
     * @return FilterableEntityInterface
     * @throws EntityException
     *
     * Finds all values of one column
     */
    public function extractColumn(string $column, string $alias = null): FilterableEntityInterface;
    /**
     * @return FilterableEntityInterface
     * @throws EntityException
     *
     * If an array with one entry exists, it creates one associative array from it
     */
    public function normalizeIfOneExists(): FilterableEntityInterface;
    /**
     * @param array $grouping
     * @param string|null $scenarioName
     * @return FilterableEntityInterface
     * @throws EntityException
     * @throws \RuntimeException
     *
     * Normalizes results from joined results.
     *
     * Example grouping:
     *
     * [
     *     'linking_column': 'id',
     *     'columns': [
     *          'column_1',
     *          'column_2',
     *     ],
     * ]
     */
    public function normalizeJoinedResult(array $grouping, string $scenarioName): FilterableEntityInterface;
}