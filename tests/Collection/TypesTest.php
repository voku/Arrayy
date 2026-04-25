<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\tests\UserData;
use Arrayy\Type\IntCollection;
use Arrayy\Type\StringCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TypesTest extends TestCase
{
    public function testShuffleSimple(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertContains('A', $set->toArray());
        static::assertContains('B', $set->toArray());
        static::assertContains('C', $set->toArray());
        static::assertContains('D', $set->toArray());
        static::assertNotContains('E', $set->toArray());
    }

    public function testTypeCheck(): void
    {
        $set = new StringCollection(['A', 'B', 'C']);

        static::assertSame(['A', 'B', 'C', 'D', 'E'], $set->push(...['D', 'E'])->getArray());
        static::assertSame(['0', '1', 'A', 'B', 'C', 'D', 'E'], $set->unshift(...['0', '1'])->getArray());
        static::assertSame('0', $set->pull(0));
        static::assertSame([1 => '1', 'A', 'B', 'C', 'D', 'E'], $set->toArray());
    }

    public function testPushTypeCheckError(): void
    {
        $set = new StringCollection(['A', 'B', 'C']);

        $this->expectException(\TypeError::class);
        /* @phpstan-ignore argument.type */
        static::assertSame(['A', 'B', 'C', 'D', 'E'], $set->push(5)->getArray());
    }

    public function testChainMethods(): void
    {
        $m = UserData::meta();
        $mCity = \Arrayy\tests\CityData::meta();

        $city = \Arrayy\tests\CityData::create([$mCity->name => 'Voerde', $mCity->plz => '46562', $mCity->infos => ['home']]);
        $data = static function () use ($city, $m) {
            yield new UserData([$m->id => 1, $m->city => clone $city, $m->firstName => 'Sven', $m->lastName => 'Moelleken']);
            yield new UserData([$m->id => 2, $m->city => clone $city, $m->firstName => 'Lars', $m->lastName => 'Moelleken']);
            yield new UserData([$m->id => 2, $m->city => clone $city, $m->firstName => 'Lea', $m->lastName => 'Moelleken']);
        };

        $users = UserDataCollection::createFromGeneratorFunction($data);
        $names = $users
            ->filter(static function (UserData $person): bool {
                return $person->id <= 30;
            })
            ->customSortValuesImmutable(static function (UserData $a, UserData $b): int {
                return $a->firstName <=> $b->firstName;
            })
            ->map(static function (UserData $person): string {
                return (string) $person->firstName;
            })
            ->implode(';');

        static::assertSame('Lars;Lea;Sven', $names);
    }

    public function testUnshiftTypeCheckError(): void
    {
        $set = new StringCollection(['A', 'B', 'C']);

        $this->expectException(\TypeError::class);
        /* @phpstan-ignore argument.type */
        static::assertSame(['A', 'B', 'C', 'D', 'E'], $set->unshift(5)->getArray());
    }

    public function testChunk(): void
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

    public function testCount(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            4,
            $set->count()
        );
    }

    public function testDiff(): void
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

        static::assertSame(
            [2 => 'C', 3 => 'D'],
            $set->diffKey($set1->toArray(), $set2->toArray())->toArray()
        );

        static::assertSame(
            [1 => 'B', 2 => 'C', 3 => 'D'],
            $set->diffKeyAndValue($set1->toArray(), $set2->toArray())->toArray()
        );
    }

    public function testEach(): void
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

    public function testFilter(): void
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

    public function testFirst(): void
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

    public function testGet(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            'A',
            $set->get(0)
        );
    }

    public function testHas(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertTrue($set->hasValue('A'));
        static::assertFalse($set->hasValue('E'));
    }

    public function testImplode(): void
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

        static::assertSame(
            '#A #B #C #D',
            $set->implode(' #', '#')
        );
    }

    public function testIntersect(): void
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

    public function testLast(): void
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

    public function testNth(): void
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

    public function testOnly(): void
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

    public function testPad(): void
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

    public function testPop(): void
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

    public function testPush(): void
    {
        $set = new StringCollection(['A', 'B', 'C']);
        $set->push('D');

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );

        $this->expectException(\TypeError::class);
        /* @phpstan-ignore argument.type */
        $set->push(1);
    }

    public function testReduce(): void
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

    public function testReverse(): void
    {
        $set = new StringCollection([1 => 'A', 'B', 'C', 'D']);

        static::assertSame(
            ['D', 'C', 'B', 'A'],
            $set->reverse()->toArray()
        );
    }

    public function testReverseKeepIndex(): void
    {
        $set = new StringCollection([1 => 'A', 'B', 'C', 'D']);

        static::assertSame(
            [4 => 'D', 3 => 'C', 2 => 'B', 1 => 'A'],
            $set->reverseKeepIndex()->toArray()
        );
    }

    public function testSearch(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            2,
            $set->searchIndex('C')
        );
    }

    public function testShift(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            'A',
            $set->shift()
        );
    }

    public function testShuffle(): void
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

    public function testSlice(): void
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

    public function testSort(): void
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

    public function testSplice(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);
        $array = $set->splice(1, 2, ['E', 'F']);

        static::assertSame(
            ['A', 'E', 'F', 'D'],
            $array->getArray()
        );
    }

    public function testToJson(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            '["A","B","C","D"]',
            $set->toJson()
        );
    }

    public function testToPermutation(): void
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

    public function testUnique(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D', 'A']);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->unique()->toArray()
        );
    }

    public function testInstanceError(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected to be of type {stdClass}, instead got value `Arrayy\\Arrayy Object');

        $testArray = [new \stdClass(), new \stdClass(), new \Arrayy\Arrayy()];

        new \Arrayy\Type\InstanceCollection(
            $testArray,
            null,
            null,
            \stdClass::class
        );
    }

    public function testInstance(): void
    {
        $testArray = [new \stdClass(), new \stdClass()];

        $set = new \Arrayy\Type\InstanceCollection(
            $testArray,
            null,
            null,
            \stdClass::class
        );

        static::assertSame(
            $testArray,
            $set->toArray()
        );
    }

    public function testInstancesError(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected to be of type {stdClass}, instead got value `Arrayy\\Arrayy Object');

        $testArray = [new \stdClass(), new \stdClass(), new \Arrayy\Arrayy()];

        new \Arrayy\Type\InstancesCollection(
            $testArray,
            null,
            null,
            [\stdClass::class]
        );
    }

    public function testInstances(): void
    {
        $testArray = [new \stdClass(), new \stdClass(), new \Arrayy\Arrayy()];

        $set = new \Arrayy\Type\InstancesCollection(
            $testArray,
            null,
            null,
            [\stdClass::class, \Arrayy\Arrayy::class]
        );

        static::assertSame(
            $testArray,
            $set->toArray()
        );
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function testWalk(): void
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
