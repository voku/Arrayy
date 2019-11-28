<?php

declare(strict_types=1);

use Arrayy\Type\StringType;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class StringTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new StringType(['A', 'B', 'C', 'D']);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(TypeError::class);

        new StringType(['A', 'B', 'C', 1]);
    }
}
