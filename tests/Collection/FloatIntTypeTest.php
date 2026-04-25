<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\FloatIntCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FloatIntTypeTest extends TestCase
{
    public function testArray(): void
    {
        $set = new FloatIntCollection([1.0, 1.1, 1.2, 1.3, 2]);

        static::assertSame(
            [1.0, 1.1, 1.2, 1.3, 2],
            $set->toArray()
        );
    }

    public function testFloatArray(): void
    {
        $set = new \Arrayy\Type\FloatIntArrayCollection([[1.0, 1.1, 1.2], [2.3, 2]]);

        static::assertSame(
            [[1.0, 1.1, 1.2], [2.3, 2]],
            $set->toArray()
        );
    }

    public function testWrongValue(): void
    {
        $this->expectException(\TypeError::class);

        /* @phpstan-ignore-next-line */
        new FloatIntCollection([1.0, 1.1, 1.2, '2']);
    }
}
