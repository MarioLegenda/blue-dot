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
     *
     * Given a $column name and a value of the column, returns an array of found values.
     *
     * For example:
     *
     * If the result of a query is:
     *     [
     *         0 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
     *         1 => ['name' => 'Katie', 'last_name' => 'Melua' ... other fields ]
     *         2 => ['name' => 'Billie', 'last_name' => 'Holiday' ... other fields ]
     *         3 => ['name' => 'Dolly', 'last_name' => 'Parton' ... other fields ]
     *         4 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
     *     ]
     *
     * if you call $entity->findBy('name', 'Natalia'), this method will return a list
     * of all results that have 'Natalia' in the name column. It is important to say that
     * this method will return an *array* of numerically indexed entries even if there is only
     * one entry.
     *
     * In this example, it will return 2 entries.
     */
    public function findBy(string $column, $value): FilterableEntityInterface
    {
        return $this->doFindBy($column, $value);
    }
    /**
     * @inheritdoc
     * @return FilterableEntityInterface|EntityInterface
     *
     * Find method returns a single entry only if a single entry actually exists in the array
     * of results. If we have a result like this one...
     *
     *     [
     *         0 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
     *         1 => ['name' => 'Katie', 'last_name' => 'Melua' ... other fields ]
     *         2 => ['name' => 'Billie', 'last_name' => 'Holiday' ... other fields ]
     *         3 => ['name' => 'Dolly', 'last_name' => 'Parton' ... other fields ]
     *         4 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
     *     ]
     *
     * $entity->find('name', 'Natalia') will throw an exception because there are 2 rows that have
     * the string 'Natalia' as the value of the name column. Wrap this method into a try/catch
     * clause to avoid the exception.
     */
    public function find(string $column, $value): FilterableEntityInterface
    {
        return $this->doFind($column, $value);
    }
    /**
     * @inheritdoc
     * @return FilterableEntityInterface|EntityInterface
     *
     * Entity::extractColumn() returns all entries of one column.
     *     [
     *         0 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
     *         1 => ['name' => 'Katie', 'last_name' => 'Melua' ... other fields ]
     *         2 => ['name' => 'Billie', 'last_name' => 'Holiday' ... other fields ]
     *         3 => ['name' => 'Dolly', 'last_name' => 'Parton' ... other fields ]
     *         4 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
     *     ]
     *
     * $entity->extractColumn('name') will return a list of all name properties like this...
     *
     * [
     *     'name' => ['Natalia', 'Katie', 'Billie', 'Dolly', 'Natalia']
     * ]
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
     *
     * This is a special method that deals only with relationships between MySQL tables.
     * If you have a one-to-many relationship, use this method to group the fields in a natural
     * way.
     *
     * For example, if we have a table *words* and a table *translations* and *translations* table
     * has a many-to-one relationship to *words*, execution a join sql query will result in the number of rows
     * that *translations* table has even if *words* has only one row for a search word.
     *
     * SELECT w.id, w.name, t.translation FROM words AS w INNER JOIN translations AS t ON w.id = t.word_id AND w.id = 1;
     *
     * If *translations* has 10 rows for a single word, this query would return 10 rows with identical information
     * from the *words* table rows and different translations. This is not what we want.
     *
     * To normalize this result, use
     *
     * $entity->normalizeJoinedResult([
     *     'linking_column' => 'id',
     *     'columns' => ['translations']
     * ]);
     *
     * 'linking_column' tells us the relationship between the rows. It the above example, 'id' would be identical for
     * all rows since it is the 'id' of the *words* table. 'columns' are the columns you would like to group. The result is
     *
     * [
     *     'id' => 1,
     *     'name' => 'word_name',
     *     'translations' => [
     *         'translation1',
     *         'translation2',
     *         ... other translations
     *     ]
     * ]
     *
     * You can choose as much columns as you like.
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
     *
     * All the above methods return the result as a numerically indexed array even if the only found
     * a single result. This method flattens or normalizes the array and returns just that single result.
     *
     *     [
     *         0 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
     *         1 => ['name' => 'Katie', 'last_name' => 'Melua' ... other fields ]
     *         2 => ['name' => 'Billie', 'last_name' => 'Holiday' ... other fields ]
     *         3 => ['name' => 'Dolly', 'last_name' => 'Parton' ... other fields ]
     *     ]
     *
     * In this example, $entity->find('name', 'Natalia') would return this...
     *
     * [
     *     0 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields
     * ]
     *
     * When $entity->normalizeIfOneExists() is applied, it would return
     *
     * [
     *     'name' => 'Natalia',
     *     'last_name' => 'Natalie'
     *     ... other fields
     * ]
     *
     * Notice that we lost the numeric index. This method only works if there is a single entry in an array
     *
     * You can chain calls to this method like this $entity->find('name', 'Natalia')->normalizeIfOneExists()
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

            if (is_string($firstKey)) {
                $result = $arguments[$firstKey];
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

        /*
         * Check if the $arguments array is an associative array with string keys
         */
        if (!empty($arguments) and is_string(array_keys($arguments)[0])) {
            if (isset($column, $arguments)) {
                $result = [$column => [0 => $arguments[$column]]];

                return new Entity($this->getName(), ['data' => $result]);
            }
        }

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

        return new Entity($this->getName(), ['data' => $result['data']]);
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