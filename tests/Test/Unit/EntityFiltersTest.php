<?php

namespace Test\Unit;

use BlueDot\Entity\BaseEntity;
use BlueDot\Entity\Entity;
use PHPUnit\Framework\TestCase;
use Test\FakerTrait;

class EntityFiltersTest extends BaseTest
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

        $entity = new Entity('name', $result);

        $idResult = $entity->findBy('id', $id)->toArray();

        static::assertEquals(1, count($idResult['data']));

        $nameResult = $entity->findBy('name', 'name')->toArray();

        static::assertEquals(5, count($nameResult['data']));
    }

    public function test_find()
    {
        $result = $this->getArrayResult(10);
        $id = 1;

        $entity = new Entity('name', $result);

        $idResult = $entity->find('id', $id)->toArray();

        static::assertEquals(1, count($idResult['data']));
    }

    public function test_extractColumn()
    {
        $result = $this->getArrayResult(10);

        $entity = new Entity('name', $result);

        $idResult = $entity->extractColumn('id')->toArray();

        static::assertArrayHasKey('data', $idResult);
        static::assertEquals(1, count($idResult['data']));
        static::assertArrayHasKey('id', $idResult['data']);
        static::assertEquals(10, count($idResult['data']['id']));
    }

    public function test_normalizeIfOneExists()
    {
        $result = $this->getArrayResult(10);
        $id = 1;

        $entity = new Entity('name', $result);

        $idResult = $entity->find('id', $id)->toArray();

        static::assertEquals(1, count($idResult['data']));

        $entity = new Entity('name', ['data' => $idResult['data']]);

        $result = $entity->normalizeIfOneExists()->toArray();

        foreach ($this->columns as $column) {
            static::assertArrayHasKey($column, $result['data']);
            static::assertNotEmpty($result['data'][$column]);
        }
    }

    public function test_normalizeJoinedResults()
    {
        $normalizationArray = $this->getNormalizationArray(10);

        $entity = new Entity('name', $normalizationArray);

        /** @var Entity $normalized */
        $normalized = $entity->normalizeJoinedResult([
            'linking_column' => 'id',
            'columns' => [
                'lastname',
                'username',
            ],
        ]);

        static::assertEquals(10, count($normalized->toArray()['data']));

        $data = $normalized->toArray()['data'];
        foreach ($data as $item) {
            static::assertGreaterThan(1, $item['lastname']);
            static::assertGreaterThan(1, $item['username']);
        }
    }

    public function test_filters_chaining()
    {
        $result = $this->getArrayResult(10);
        $id = 1;

        $entity = new Entity('name', $result);

        $idResult = $entity
            ->find('id', $id)
            ->normalizeIfOneExists()
            ->extractColumn('lastname')
            ->toArray();

        static::arrayHasKey('data', $idResult);
        static::assertCount(1, $idResult['data']['lastname']);
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