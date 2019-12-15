<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\BoolCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class BoolTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new BoolCollection([true, true, false, false]);

        static::assertSame(
            [true, true, false, false],
            $set->toArray()
        );
    }

    public function testBoolArray()
    {
        $set = new \Arrayy\Type\BoolArrayCollection([[true, true], [false, false]]);

        static::assertSame(
            [[true, true], [false, false]],
            $set->toArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        new BoolCollection([true, true, false, 1]);
    }

    public function testBoolArrayWrongValue()
    {
        $this->expectException(\TypeError::class);

        new BoolCollection([[true, true], false, [true]]);
    }
}
