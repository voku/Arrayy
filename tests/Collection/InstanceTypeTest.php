<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

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

        // ---

        $collection = InstanceCollection::construct(
            TypeInterface::class,
            [new \Arrayy\Type\StringCollection(['A', 'B', 'C']), new \Arrayy\Type\IntCollection([1])]
        );

        static::assertEquals(
            [['A', 'B', 'C'], [1]],
            $collection->toArray(true)
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        \Arrayy\Type\InstancesCollection::construct(
            TypeInterface::class,
            [new BoolCollection(), new \stdClass()]
        );
    }
}
