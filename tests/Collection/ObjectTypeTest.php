<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ObjectTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new \Arrayy\Type\ObjectCollection([
            new \stdClass(),
            new \Arrayy\Arrayy(),
            new self(),
        ]);

        static::assertEquals(
            [
                new \stdClass(),
                new \Arrayy\Arrayy(),
                new self(),
            ],
            $set->getArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        /* @phpstan-ignore-next-line */
        new \Arrayy\Type\ObjectCollection(['strtolower', 1]);
    }
}
