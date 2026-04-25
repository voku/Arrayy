<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class NumericStringTypeTest extends TestCase
{
    public function testArray(): void
    {
        $set = new \Arrayy\Type\NumericStringCollection(
            [
                '1.0',
                '1.2',
                '4',
            ]
        );

        static::assertSame(
            [
                '1.0',
                '1.2',
                '4',
            ],
            $set->getArray()
        );
    }

    public function testWrongValue(): void
    {
        $this->expectException(\TypeError::class);

        /* @phpstan-ignore argument.type */
        new \Arrayy\Type\NumericStringCollection([
            3.2,
            2,
            1
        ]);
    }
}
