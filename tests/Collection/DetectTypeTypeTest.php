<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\DetectFirstValueTypeCollection;
use Arrayy\Type\MixedCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DetectTypeTypeTest extends TestCase
{
    public function testArrayDetectString()
    {
        $set = new DetectFirstValueTypeCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testArrayDetectInteger()
    {
        $set = new DetectFirstValueTypeCollection([1, 2, 3, 4]);

        static::assertSame(
            [1, 2, 3, 4],
            $set->toArray()
        );
    }

    public function testArrayDetectClass()
    {
        $set = new DetectFirstValueTypeCollection([new \stdClass(), new \stdClass()]);

        static::assertEquals(
            [new \stdClass(), new \stdClass()],
            $set->toArray()
        );
    }

    public function testArrayDetectTraversable()
    {
        $set = new DetectFirstValueTypeCollection(new MixedCollection(['A', 'B', 'C', 'D']));

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testWrongValueDetectString()
    {
        $this->expectException(\TypeError::class);

        new DetectFirstValueTypeCollection(['A', 'B', 'C', 1]);
    }

    public function testWrongValueDetectInteger()
    {
        $this->expectException(\TypeError::class);

        new DetectFirstValueTypeCollection([1, 2, 3, 4.0]);
    }

    public function testWrongValueDetectClass()
    {
        $this->expectException(\TypeError::class);

        new DetectFirstValueTypeCollection([new \stdClass(), new DetectFirstValueTypeCollection()]);
    }

    public function testWrongValueDetectTraversable()
    {
        $this->expectException(\TypeError::class);

        new DetectFirstValueTypeCollection(new MixedCollection(['A', 'B', 'C', 1]));
    }
}
