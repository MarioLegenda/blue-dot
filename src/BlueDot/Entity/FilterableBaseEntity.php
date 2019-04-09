<?php


namespace BlueDot\Entity;

use BlueDot\Common\AbstractArgumentBag;
use BlueDot\Common\Util\Util;
use BlueDot\Exception\EntityException;

class FilterableBaseEntity extends BaseEntity implements FilterableEntityInterface
{
    /**
     * @inheritdoc
     * @return FilterableEntityInterface|EntityInterface
     */
    public function findBy(string $column, $value): FilterableEntityInterface
    {
        return $this->doFindBy($column, $value);
    }
    /**
     * @inheritdoc
     * @return FilterableEntityInterface|EntityInterface
     */
    public function find(string $column, $value): FilterableEntityInterface
    {
        return $this->doFind($column, $value);
    }
    /**
     * @inheritdoc
     * @return FilterableEntityInterface|EntityInterface
     */
    public function extractColumn(
        string $column,
        string $alias = null
    ): FilterableEntityInterface {
        return $this->doExtractColumn($column, $alias);
    }

    /**
     * @inheritdoc
     * @return FilterableEntityInterface|EntityInterface
     */
    public function normalizeJoinedResult(
        array $grouping,
        string $scenarioName = null
    ): FilterableEntityInterface {
        return $this->doNormalizeJoinedResult($grouping, $scenarioName);
    }
    /**
     * @inheritdoc
     * @return FilterableEntityInterface|EntityInterface
     */
    public function normalizeIfOneExists() : FilterableEntityInterface
    {
        $arguments = $this->data['data'];

        $result = [];
        if (count($arguments) === 1) {
            if (is_object($arguments[0])) {
                $message = sprintf(
                    'Invalid argument. You cannot normalize an object in Entity::normalizeIfOneExists()'
                );

                throw new EntityException($message);
            }

            $firstKey = array_keys($arguments)[0];

            if (is_int($firstKey)) {
                $result = $arguments[0];
            }
        }

        return new Entity($this->getName(), ['data' => $result]);
    }
    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->data['data']);
    }

    /**
     * @param string $column
     * @param string|null $alias
     * @param array|null $replacementResult
     * @return FilterableEntityInterface
     * @throws EntityException
     */
    private function doExtractColumn(
        string $column,
        string $alias = null,
        array $replacementResult = null
    ): FilterableEntityInterface {
        $result = array();

        $arguments = $this->data['data'];
        if (!empty($replacementResult)) {
            $arguments = $replacementResult;
        }

        $argumentsGenerator = Util::instance()->createGenerator($arguments);
        foreach ($argumentsGenerator as $item) {
            $argument = $item['item'];

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

        return new Entity($this->getName(), ['data' => $result]);
    }
    /**
     * @param string $column
     * @param $value
     * @param array|null $replacementResult
     * @return FilterableEntityInterface
     * @throws EntityException
     */
    private function doFindBy(
        string $column,
        $value,
        array $replacementResult = null
    ): FilterableEntityInterface {
        $arguments = $this->data['data'];
        if (!empty($replacementResult)) {
            $arguments = $replacementResult;
        }

        $results = array();
        $argumentsGenerator = Util::instance()->createGenerator($arguments);
        foreach ($argumentsGenerator as $item) {
            $argument = $item['item'];

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

        return new Entity($this->getName(), ['data' => $results]);
    }
    /**
     * @param array $grouping
     * @param string|null $scenarioName
     * @return FilterableEntityInterface
     * @throws EntityException
     */
    private function doNormalizeJoinedResult(array $grouping, string $scenarioName = null)
    {
        $arguments = $this->normalize();

        $grouping = new Grouping($grouping);

        $traversedValues = array();
        $result = array();
        $temp = array();
        $argumentsGenerator = Util::instance()->createGenerator($arguments);
        foreach ($argumentsGenerator as $item) {
            $argument = $item['item'];

            if (array_key_exists($grouping->getLinkingColumn(), $argument)) {
                $linkingColumn = $grouping->getLinkingColumn();
                $columnValue = $argument[$linkingColumn];
                $columns = $grouping->getColumns();

                if (in_array($columnValue, $traversedValues) === true) {
                    continue;
                }

                /** @var Entity $foundResults */
                $foundResults = $this->doFindBy($linkingColumn, $columnValue, $arguments);
                $extracted = array();

                foreach ($columns as $column) {
                    /** @var Entity $extractedColumnValues */
                    $extractedColumnValues = $this->doExtractColumn($column, null, $foundResults->toArray()['data']);

                    $extractedColumnValuesArray = $extractedColumnValues->toArray()['data'];

                    if (!empty($extractedColumnValuesArray)) {
                        $extracted[$column] = array($column => array_unique($extractedColumnValuesArray[$column]));
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

        return new Entity($this->getName(), ['data' => $result]);
    }
    /**
     * @param string $column
     * @param $value
     * @return FilterableEntityInterface
     * @throws EntityException
     */
    private function doFind(
        string $column,
        $value
    ):FilterableEntityInterface {
        $result = $this->findBy($column, $value)->toArray();

        if (count($result) !== 1) {
            throw new EntityException(
                sprintf(
                    'Invalid entity result. Entity::find() can only return a single result'
                )
            );
        }

        if (is_object($result['data'][0])) {
            return $result['data'][0];
        }

        return new Entity($this->getName(), ['data' => $result]);
    }
    /**
     * @param string|null $scenarioName
     * @throws \RuntimeException
     * @return array|AbstractArgumentBag
     */
    private function normalize(string $scenarioName = null)
    {
        $arguments = $this->data['data'];
        if (!is_null($scenarioName)) {
            $arguments = $this->get($scenarioName);

            if ($arguments instanceof BaseEntity) {
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