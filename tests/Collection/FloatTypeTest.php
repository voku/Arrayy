<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\FloatCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FloatTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new FloatCollection([1.0, 1.1, 1.2, 1.3, 2.0]);

        static::assertSame(
            [1.0, 1.1, 1.2, 1.3, 2.0],
            $set->toArray()
        );
    }

    public function testFloatArray()
    {
        $set = new \Arrayy\Type\FloatArrayCollection([[1.0, 1.1, 1.2], [2.3, 2.0]]);

        static::assertSame(
            [[1.0, 1.1, 1.2], [2.3, 2.0]],
            $set->toArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        /* @phpstan-ignore-next-line */
        new FloatCollection([1.0, 1.1, 1.2, '2']);
    }
}
