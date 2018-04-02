<?php

namespace BlueDot\Result;

class IntConverter
{
    /**
     * @param \Generator $generator
     * @return array
     * @throws \RuntimeException
     */
    public static function convert(\Generator $generator): array
    {
        $converted = [];
        foreach ($generator as $item) {
            $items = $item['item'];

            if (!is_array($items)) {
                $message = sprintf(
                    '\'%s\' can only be used with database query results which are multidimensional arrays',
                    IntConverter::class
                );

                throw new \RuntimeException($message);
            }

            $temp = [];
            foreach ($items as $key => $i) {
                if (is_numeric($i)) {
                    $temp[$key] = (int) $i;

                    continue;
                }

                $temp[$key] = $i;
            }

            $converted[] = $temp;
        }

        return $converted;
    }
}