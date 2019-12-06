<?php

declare(strict_types=1);

use Arrayy\Collection\Collection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TypeTypeTest extends TestCase
{
    public function testArray()
    {
        $set = Collection::construct('string', ['A', 'B', 'C', 'D']);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(TypeError::class);

        /** @noinspection PhpStrictTypeCheckingInspection */
        new Collection(stdClass::class, [new stdClass(), 'A']);
    }
}
