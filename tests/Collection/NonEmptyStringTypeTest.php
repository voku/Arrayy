<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class NonEmptyStringTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new \Arrayy\Type\NonEmptyStringCollection(
            [
                'foo',
                '1',
                'bar',
            ]
        );

        static::assertSame(
            [
                'foo',
                '1',
                'bar',
            ],
            $set->getArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        /** @phpstan-ignore-next-line */
        new \Arrayy\Type\NonEmptyStringCollection([
           'foo',
           '',
           'bar',
        ]);
    }
}
