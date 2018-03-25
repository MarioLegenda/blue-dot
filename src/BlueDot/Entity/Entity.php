<?php

namespace BlueDot\Entity;

use BlueDot\Common\AbstractArgumentBag;
use BlueDot\Common\ArgumentBag;
use BlueDot\Exception\EntityException;

class Entity extends AbstractArgumentBag
{
    /**
     * @param string $column
     * @param mixed $value
     * @return array
     * @throws EntityException
     *
     * Finds multiple results by column and value and returns a an array
     */
    public function findBy(string $column, $value) : array
    {
        return $this->doFindBy($column, $value);
    }
    /**
     * @param string $column
     * @param $value
     * @throws EntityException
     * @return mixed
     *
     * Finds a single result from an array of result by its value and returns a new Entity
     */
    public function find(string $column, $value)
    {
        return $this->doFind($column, $value);
    }
    /**
     * @param string $column
     * @param string|null $alias
     * @return array
     * @throws EntityException
     */
    public function extractColumn(string $column, string $alias = null) : array
    {
        return $this->doExtractColumn($column, $alias);
    }
    /**
     * @param array $grouping
     * @param string $scenarioName
     * @return array
     * @throws EntityException
     */
    public function normalizeJoinedResult(array $grouping, string $scenarioName = null)
    {
        return $this->doNormalizeJoinedResult($grouping, $scenarioName);
    }
    /**
     * @return Entity
     * @throws EntityException
     */
    public function normalizeIfOneExists() : Entity
    {
        if (count($this->arguments) === 1) {
            if (is_object($this->arguments[0])) {
                throw new EntityException(
                    sprintf('Invalid argument. You cannot normalize an object in Entity::normalizeIfOneExists()')
                );
            }

            $firstKey = array_keys($this->arguments)[0];

            if (is_int($firstKey)) {
                $this->arguments = $this->arguments[0];
            }
        }

        return $this;
    }

    private function doExtractColumn(string $column, string $alias = null, array $replacementResult = null)
    {
        $result = array();

        $arguments = $this->arguments;
        if (!empty($replacementResult)) {
            $arguments = $replacementResult;
        }

        foreach ($arguments as $argument) {
            if (is_object($argument)) {
                $method = 'get'.str_replace('_', '', ucwords($column, '_'));

                if (!method_exists($argument, $method)) {
                    throw new EntityException(
                        sprintf(
                            'Invalid method. Method \'s\' does not exist on object %s in Entity::extractColumn()',
                            $method,
                            get_class($argument)
                        )
                    );
                }

                $value = $argument->{$method}();

                if (is_string($alias)) {
                    $result[$alias][] = $value;
                } else {
                    $result[$column][] = $value;
                }

                continue;
            }

            if (array_key_exists($column, $argument)) {
                if (is_string($alias)) {
                    $result[$alias][] = $argument[$column];
                } else {
                    $result[$column][] = $argument[$column];
                }
            }
        }

        return $result;
    }
    /**
     * @param string $column
     * @param $value
     * @param array|null $replacementResult
     * @return array
     * @throws EntityException
     */
    private function doFindBy(string $column, $value, array $replacementResult = null)
    {
        $arguments = $this->arguments;
        if (!empty($replacementResult)) {
            $arguments = $replacementResult;
        }

        $results = array();
        foreach ($arguments as $argument) {
            if (is_object($argument)) {
                $method = 'get'.str_replace('_', '', ucwords($column, '_'));

                if (!method_exists($argument, $method)) {
                    throw new EntityException(
                        sprintf(
                            'Invalid method. Method \'s\' does not exist on object %s in Entity::findBy()',
                            $method,
                            get_class($argument)
                        )
                    );
                }

                $argValue = $argument->{$method}();

                if ($argValue == $value) {
                    $results[] = $argument;
                }
            }

            if (array_key_exists($column, $argument)) {
                $argValue = $argument[$column];

                if ($argValue == $value) {
                    $results[] = $argument;
                }
            }
        }

        return $results;
    }
    /**
     * @param array $grouping
     * @param string|null $scenarioName
     * @return array
     * @throws EntityException
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     */
    private function doNormalizeJoinedResult(array $grouping, string $scenarioName = null)
    {
        $arguments = $this->normalize();

        $grouping = new Grouping($grouping);

        $traversedValues = array();
        $result = array();
        $temp = array();
        foreach ($arguments as $argument) {
            if (array_key_exists($grouping->getLinkingColumn(), $argument)) {
                $linkingColumn = $grouping->getLinkingColumn();
                $columnValue = $argument[$linkingColumn];
                $columns = $grouping->getColumns();

                if (in_array($columnValue, $traversedValues) === true) {
                    continue;
                }

                $foundResults = $this->doFindBy($linkingColumn, $columnValue, $arguments);
                $extracted = array();

                foreach ($columns as $column) {
                    $extractedColumnValues = $this->doExtractColumn($column, null, $foundResults);

                    if (!empty($extractedColumnValues)) {
                        $extracted[$column] = array($column => array_unique($extractedColumnValues[$column]));
                    }
                }

                $argumentKeys = array_keys($argument);
                $diff = array_diff($argumentKeys, $columns);

                foreach ($diff as $c) {
                    $temp[$c] = $argument[$c];
                }

                foreach ($extracted as $c => $e) {
                    $temp[$c] = $e[$c];
                }

                $result[] = $temp;

                $traversedValues[] = $columnValue;
            }
        }

        return $result;
    }
    /**
     * @param string $column
     * @param $value
     * @return array|mixed
     * @throws EntityException
     */
    private function doFind(string $column, $value)
    {
        $result = $this->findBy($column, $value);

        if (count($result) !== 1) {
            throw new EntityException(
                sprintf(
                    'Invalid entity result. Entity::find() can only return a single result'
                )
            );
        }

        if (is_object($result[0])) {
            return $result[0];
        }

        return $result;
    }
    /**
     * @param string|null $scenarioName
     * @throws \BlueDot\Exception\BlueDotRuntimeException
     * @throws \RuntimeException
     * @return array|AbstractArgumentBag
     */
    private function normalize(string $scenarioName = null)
    {
        $arguments = $this->arguments;
        if (!is_null($scenarioName)) {
            $arguments = $this->get($scenarioName);

            if ($arguments instanceof Entity) {
                $arguments = $arguments->toArray();
            }
        }

        if ($arguments instanceof AbstractArgumentBag) {
            if (is_null($arguments->get('row_count'))) {
                throw new \RuntimeException('There are no rows to normalize');
            }
        }

        if (is_array($arguments) and empty($arguments)) {
            throw new \RuntimeException('There are no rows to normalize');
        }

        return $arguments;
    }
}