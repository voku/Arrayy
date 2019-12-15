<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\IntCollection;
use Arrayy\Type\StringCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TypesTest extends TestCase
{
    public function shuffle()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertIsArray($set->toArray());
    }

    public function testChunk()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D', 'E']);

        $newSet = $set->chunk(2);

        static::assertSame(
            [
                ['A', 'B'],
                ['C', 'D'],
                ['E'],
            ],
            $newSet->toArray()
        );
    }

    public function testCount()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            4,
            $set->count()
        );
    }

    public function testDiff()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);
        $set1 = new StringCollection(['A', 'C']);
        $set2 = new StringCollection(['D']);

        static::assertSame(
            [1 => 'B', 3 => 'D'],
            $set->diff($set1->toArray())->toArray()
        );

        static::assertSame(
            [1 => 'B'],
            $set->diff($set1->toArray(), $set2->toArray())->toArray()
        );
    }

    public function testEach()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        $newMap = $set->each(
            static function ($item) {
                return '_' . $item . '_';
            }
        );

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );

        static::assertSame(
            ['_A_', '_B_', '_C_', '_D_'],
            $newMap->toArray()
        );
    }

    public function testFilter()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        $newMap = $set->filter(
            static function ($item) {
                return \in_array($item, ['A', 'D'], true);
            }
        );

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );

        static::assertSame(
            [0 => 'A', 3 => 'D'],
            $newMap->toArray()
        );
    }

    public function testFirst()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            'A',
            $set->first()
        );

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testGet()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            'A',
            $set->get(0)
        );
    }

    public function testHas()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertTrue($set->hasValue('A'));
        static::assertFalse($set->hasValue('E'));
    }

    public function testImplode()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            'ABCD',
            $set->implode()
        );

        static::assertSame(
            'A_B_C_D',
            $set->implode('_')
        );
    }

    public function testIntersect()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);
        $set1 = new StringCollection(['A', 'C']);
        $set2 = new StringCollection(['A']);

        static::assertSame(
            ['A', 'C'],
            $set->intersection($set1->toArray())->toArray()
        );

        static::assertSame(
            ['A'],
            $set->intersectionMulti($set1->toArray(), $set2->getArray())->getArray()
        );
    }

    public function testLast()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            'D',
            $set->last()
        );

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testNth()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            [],
            $set->nth(1, 2)->toArray()
        );

        static::assertSame(
            [0 => 'A', 2 => 'C'],
            $set->nth(2, 0)->toArray()
        );

        static::assertSame(
            [0 => 'A', 2 => 'C'],
            $set->nth(2)->toArray()
        );
    }

    public function testOnly()
    {
        $set = new StringCollection(['A', 'c' => 'B']);

        static::assertSame(
            [],
            $set->only([4, 'C'])->toArray()
        );

        static::assertSame(
            ['A', 'c' => 'B'],
            $set->only([0, 'c'])->toArray()
        );
    }

    public function testPad()
    {
        $set = new StringCollection(['A', 'B']);

        static::assertSame(
            ['A', 'B', 'C', 'C'],
            $set->pad(4, 'C')->toArray()
        );

        static::assertSame(
            ['C', 'C', 'A', 'B'],
            $set->pad(-4, 'C')->toArray()
        );
    }

    public function testPop()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            'D',
            $set->pop()
        );

        static::assertSame(
            ['A', 'B', 'C'],
            $set->toArray()
        );
    }

    public function testPush()
    {
        $set = new StringCollection(['A', 'B', 'C']);
        $set->push('D');

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );

        $this->expectException(\TypeError::class);
        $set->push(1);
    }

    public function testReduce()
    {
        $set = new IntCollection([1, 2, 3, 4]);

        $value = $set->reduce(
            static function ($carry, $item) {
                return $carry * $item;
            },
            10
        )->getArray();

        static::assertSame(
            [240],
            $value
        );
    }

    public function testReverse()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            ['D', 'C', 'B', 'A'],
            $set->reverse()->toArray()
        );
    }

    public function testSearch()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            2,
            $set->searchIndex('C')
        );
    }

    public function testShift()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            'A',
            $set->shift()
        );
    }

    public function testShuffle()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        $newSet = $set->shuffle();

        static::assertContains(
            'A',
            $newSet->toArray()
        );

        static::assertContains(
            'B',
            $newSet->toArray()
        );

        static::assertContains(
            'C',
            $newSet->toArray()
        );

        static::assertContains(
            'D',
            $newSet->toArray()
        );
    }

    public function testSlice()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            ['C', 'D'],
            $set->slice(2)->toArray()
        );

        static::assertSame(
            ['B', 'C'],
            $set->slice(-3, 2)->toArray()
        );
    }

    public function testSort()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            ['D', 'C', 'B', 'A'],
            $set->customSortValues(
                static function ($a, $b) {
                    return -1 * \strcmp($a, $b);
                }
            )->toArray()
        );
    }

    public function testSplice()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);
        $array = $set->splice(1, 2, ['E', 'F']);

        static::assertSame(
            ['A', 'E', 'F', 'D'],
            $array->getArray()
        );
    }

    public function testToJson()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            '["A","B","C","D"]',
            $set->toJson()
        );
    }

    public function testToPermutation()
    {
        $set = new StringCollection(['A', 'B', 'C']);

        static::assertSame(
            [
                0 => [
                    0 => 'A',
                    1 => 'B',
                    2 => 'C',
                ],
                1 => [
                    0 => 'B',
                    1 => 'A',
                    2 => 'C',
                ],
                2 => [
                    0 => 'A',
                    1 => 'C',
                    2 => 'B',
                ],
                3 => [
                    0 => 'C',
                    1 => 'A',
                    2 => 'B',
                ],
                4 => [
                    0 => 'B',
                    1 => 'C',
                    2 => 'A',
                ],
                5 => [
                    0 => 'C',
                    1 => 'B',
                    2 => 'A',
                ],
            ],
            $set->toPermutation()->toArray()
        );
    }

    public function testUnique()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D', 'A']);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->unique()->toArray()
        );
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function testWalk()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        $set->walk(
            static function (&$item, $index) {
                $item = '_' . $item . '_';
            }
        );

        static::assertSame(
            ['_A_', '_B_', '_C_', '_D_'],
            $set->toArray()
        );
    }
}
