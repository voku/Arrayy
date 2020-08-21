<?php

namespace Arrayy\tests\Collection;

use Arrayy\Collection\Collection;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckSimple;

/**
 * @template TKey of array-key
 * @template T
 * @extends  Collection<TKey,\stdClass::class>
 */
class StdBaseClassCollection extends Collection
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * {@inheritdoc}
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
