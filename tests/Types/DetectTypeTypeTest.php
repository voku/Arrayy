<?php

declare(strict_types=1);

use Arrayy\Type\DetectTypeCollection;
use Arrayy\Type\MixedCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DetectTypeTypeTest extends TestCase
{
    public function testArrayDetectString()
    {
        $set = new DetectTypeCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testArrayDetectInteger()
    {
        $set = new DetectTypeCollection([1, 2, 3, 4]);

        static::assertSame(
            [1, 2, 3, 4],
            $set->toArray()
        );
    }

    public function testArrayDetectClass()
    {
        $set = new DetectTypeCollection([new stdClass(), new stdClass()]);

        static::assertEquals(
            [new stdClass(), new stdClass()],
            $set->toArray()
        );
    }

    public function testArrayDetectTraversable()
    {
        $set = new DetectTypeCollection(new MixedCollection(['A', 'B', 'C', 'D']));

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testWrongValueDetectString()
    {
        $this->expectException(TypeError::class);

        new DetectTypeCollection(['A', 'B', 'C', 1]);
    }

    public function testWrongValueDetectInteger()
    {
        $this->expectException(TypeError::class);

        new DetectTypeCollection([1, 2, 3, 4.0]);
    }

    public function testWrongValueDetectClass()
    {
        $this->expectException(TypeError::class);

        new DetectTypeCollection([new stdClass(), new DetectTypeCollection()]);
    }

    public function testWrongValueDetectTraversable()
    {
        $this->expectException(TypeError::class);

        new DetectTypeCollection(new MixedCollection(['A', 'B', 'C', 1]));
    }
}
