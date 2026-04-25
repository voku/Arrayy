<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ArrayTypeTest extends TestCase
{
    public function testArray(): void
    {
        $set = new ArrayCollection([['a', 1, 1.4], [], [true, new \stdClass()], []]);

        static::assertEquals(
            [['a', 1, 1.4], [], [true, new \stdClass()], []],
            $set->toArray()
        );
    }

    public function testStringArrayFalse(): void
    {
        $this->expectException(\TypeError::class);

        /* @phpstan-ignore argument.type */
        $set = new \Arrayy\Type\StringArrayCollection([['a', 1, 1.4], [], [true, new \stdClass()], []]);

        static::assertEquals(
            [['a', 1, 1.4], [], [true, new \stdClass()], []],
            $set->toArray()
        );
    }

    public function testStringArray(): void
    {
        $set = new \Arrayy\Type\StringArrayCollection([['a', 'foo']]);

        static::assertEquals(
            [['a', 'foo']],
            $set->toArray()
        );
    }

    public function testWrongValue(): void
    {
        $this->expectException(\TypeError::class);

        /* @phpstan-ignore argument.type */
        new ArrayCollection([['a', 1, 1.4], [], [true, new \stdClass()], '[]']);
    }
}
