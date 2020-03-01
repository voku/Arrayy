<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ResourceTypeTest extends TestCase
{
    public function testArray()
    {
        $f1 = \tmpfile();
        $f2 = \tmpfile();
        $f3 = \tmpfile();

        $set = new \Arrayy\Type\ResourceCollection(
            [
                $f1,
                $f2,
                $f3,
            ]
        );

        static::assertSame(
            [
                $f1,
                $f2,
                $f3,
            ],
            $set->getArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        new \Arrayy\Type\ResourceCollection([
            new \stdClass(),
            \tmpfile(),
            \tmpfile(),
        ]);
    }
}
