<?php

namespace Test\Unit;

use BlueDot\Entity\Entity;
use PHPUnit\Framework\TestCase;
use Test\FakerTrait;

class EntityFiltersTest extends TestCase
{
    use FakerTrait;
    /**
     * @var array $columns
     */
    private $columns = [
        'id',
        'name',
        'lastname',
        'username',
        'gender',
        'address',
    ];

    public function test_findBy()
    {
        $result = $this->getArrayResult(10);
        $id = 1;

        $entity = new Entity($result);

        $idResult = $entity->findBy('id', $id);

        static::assertEquals(1, count($idResult));

        $nameResult = $entity->findBy('name', 'name');

        static::assertEquals(5, count($nameResult));
    }

    public function test_find()
    {
        $result = $this->getArrayResult(10);
        $id = 1;

        $entity = new Entity($result);

        $idResult = $entity->find('id', $id);

        static::assertEquals(1, count($idResult));
    }

    public function test_extractColumn()
    {
        $result = $this->getArrayResult(10);

        $entity = new Entity($result);

        $idResult = $entity->extractColumn('id');

        static::assertEquals(1, count($idResult));
        static::assertArrayHasKey('id', $idResult);
        static::assertEquals(10, count($idResult['id']));
    }

    public function test_normalizeIfOneExists()
    {
        $result = $this->getArrayResult(10);
        $id = 1;

        $entity = new Entity($result);

        $idResult = $entity->find('id', $id);

        static::assertEquals(1, count($idResult));

        $entity = new Entity($idResult);

        $entity->normalizeIfOneExists();

        foreach ($this->columns as $column) {
            static::assertTrue($entity->has($column));
            static::assertNotEmpty($entity->get($column));
        }
    }

    public function test_normalizeJoinedResults()
    {
        $normalizationArray = $this->getNormalizationArray(10);

        $entity = new Entity($normalizationArray);

        $normalized = $entity->normalizeJoinedResult([
            'linking_column' => 'id',
            'columns' => [
                'lastname',
                'username',
            ],
        ]);

        static::assertEquals(10, count($normalized));

        foreach ($normalized as $item) {
            static::assertGreaterThan(1, $item['lastname']);
            static::assertGreaterThan(1, $item['username']);
        }
    }

    /**
     * @param int $numOfEntries
     * @return array
     */
    private function getArrayResult(int $numOfEntries): array
    {
        $entries = [];
        for ($i = 0; $i < $numOfEntries; $i++) {
            $temp = [];

            foreach ($this->columns as $column) {
                if ($column === 'id') {
                    $temp[$column] = $i;

                    continue;
                }

                if ($column === 'name') {
                    if (($i % 2) === 0) {
                        $temp[$column] = 'name';

                        continue;
                    }
                }

                $temp[$column] = $this->getFaker()->name;
            }

            $entries[] = $temp;
        }

        return ['data' => $entries];
    }
    /**
     * @param int $numOfEntries
     * @return array
     */
    private function getNormalizationArray(int $numOfEntries): array
    {
        $normalization = [];
        for ($i = 0; $i < $numOfEntries; $i++) {
            $normalization = array_merge($normalization, $this->getArrayResult($numOfEntries)['data']);
        }

        return ['data' => $normalization];
    }
}