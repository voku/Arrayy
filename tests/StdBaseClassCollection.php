<?php

namespace Arrayy\tests;

use Arrayy\Collection\Collection;
use Arrayy\Collection\CollectionInterface;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckSimple;

/**
 * @phpstan-implements CollectionInterface<\stdClass>
 */
class StdBaseClassCollection extends Collection implements CollectionInterface
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return TypeCheckArray
     */
    public function getType()
    {
        return TypeCheckArray::create(
            [
                new TypeCheckSimple(\stdClass::class),
            ]
        );
    }
}
