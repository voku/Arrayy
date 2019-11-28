<?php

declare(strict_types=1);

use Arrayy\Type\IntCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class IntegerTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new IntCollection([1, 2, 3, 4]);

        static::assertSame(
            [1, 2, 3, 4],
            $set->toArray()
        );
    }

    public function testIntArray()
    {
        $set = new \Arrayy\Type\IntArrayCollection([[1, 2], [3, 4]]);

        static::assertSame(
            [[1, 2], [3, 4]],
            $set->toArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(TypeError::class);

        new IntCollection([1, 2, 3, 4.0]);
    }

    public function testIntArrayWrongValue()
    {
        $this->expectException(TypeError::class);

        new IntCollection([[1, 2], 4.0]);
    }
}
