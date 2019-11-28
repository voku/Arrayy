<?php

declare(strict_types=1);

use Arrayy\Type\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ArrayTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new ArrayCollection([['a', 1, 1.4], [], [true, new stdClass()], []]);

        static::assertEquals(
            [['a', 1, 1.4], [], [true, new stdClass()], []],
            $set->toArray()
        );
    }

    public function testStringArray()
    {
        $this->expectException(TypeError::class);

        $set = new \Arrayy\Type\StringArrayCollection([['a', 1, 1.4], [], [true, new stdClass()], []]);

        static::assertEquals(
            [['a', 1, 1.4], [], [true, new stdClass()], []],
            $set->toArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(TypeError::class);

        new ArrayCollection([['a', 1, 1.4], [], [true, new stdClass()], '[]']);
    }
}
