<?php

declare(strict_types=1);

use Arrayy\Type\BoolCollection;
use Arrayy\Type\InstanceCollection;
use Arrayy\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class InstanceTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new InstanceCollection(
            [new BoolCollection(), new BoolCollection()],
            null,
            null,
            TypeInterface::class
        );

        static::assertEquals(
            [new BoolCollection(), new BoolCollection()],
            $set->toArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(TypeError::class);

        InstanceCollection::construct(TypeInterface::class, [new BoolCollection(), new stdClass()]);
    }
}
