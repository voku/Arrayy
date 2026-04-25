<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\IntCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class IntegerTypeTest extends TestCase
{
    public function testArray(): void
    {
        $set = new IntCollection([1, 2, 3, 4]);

        static::assertSame(
            [1, 2, 3, 4],
            $set->toArray()
        );
    }

    public function testIntArray(): void
    {
        $set = new \Arrayy\Type\IntArrayCollection([[1, 2], [3, 4]]);

        static::assertSame(
            [[1, 2], [3, 4]],
            $set->toArray()
        );
    }

    public function testWrongValue(): void
    {
        $this->expectException(\TypeError::class);

        /* @phpstan-ignore argument.type */
        new IntCollection([1, 2, 3, 4.0]);
    }

    public function testIntArrayWrongValue(): void
    {
        $this->expectException(\TypeError::class);

        /* @phpstan-ignore argument.type */
        new IntCollection([[1, 2], 4.0]);
    }
}
