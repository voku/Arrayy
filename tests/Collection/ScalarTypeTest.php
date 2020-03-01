<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ScalarTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new \Arrayy\Type\ScalarCollection([
            '4',
            5.0,
            7,
            true,
            false,
            '6',
            '7',
        ]);

        static::assertEquals(
            [
                '4',
                5.0,
                7,
                true,
                false,
                '6',
                '7',
            ],
            $set->getArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        new \Arrayy\Type\ScalarCollection([new \stdClass(), 1]);
    }
}
