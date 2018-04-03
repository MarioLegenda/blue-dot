<?php

namespace BlueDot\Database;

class TypeConverter
{
    /**
     * @param array $values
     * @return array
     */
    public function convert(array $values): array
    {
        $converted = [];
        foreach ($values as $value) {
            $columns = array_keys($value);
            $temp = [];
            foreach ($columns as $column) {
                if (is_numeric($value[$column])) {
                    $temp[$column] = (int) $value[$column];

                    continue;
                }

                $temp[$column] = $value[$column];
            }

            $converted[] = $temp;
        }

        return $converted;
    }


}