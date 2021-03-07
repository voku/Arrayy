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

        $test = null;
        foreach ($set->getIterator() as $foo) {
            /** @phpstan-ignore-next-line */
            if ($foo === '1') {
                $test = false;
            }

            if ($foo[0] === true) {
                $test = true;
            }
        }
        assert(is_bool($test));

        static::assertTrue($test);
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        /** @phpstan-ignore-next-line */
        new BoolCollection([true, true, false, 1]);
    }

    public function testBoolArrayWrongValue()
    {
        $this->expectException(\TypeError::class);

        /** @phpstan-ignore-next-line */
        new BoolCollection([[true, true], false, [true]]);
    }
}
