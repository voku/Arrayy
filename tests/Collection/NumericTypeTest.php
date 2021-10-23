<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class NumericTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new \Arrayy\Type\NumericCollection(
            [
                '1.0',
                1.2,
                4,
            ]
        );

        static::assertSame(
            [
                '1.0',
                1.2,
                4,
            ],
            $set->getArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        /** @phpstan-ignore-next-line */
        new \Arrayy\Type\NumericCollection([
            new \stdClass(),
            \tmpfile(),
            1
        ]);
    }
}
