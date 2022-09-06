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
        /** @var resource $f1 */
        $f1 = \tmpfile();
        /** @var resource $f2 */
        $f2 = \tmpfile();
        /** @var resource $f3 */
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

        /* @phpstan-ignore-next-line */
        new \Arrayy\Type\ResourceCollection([
            new \stdClass(),
            \tmpfile(),
            \tmpfile(),
        ]);
    }
}
