<?php

namespace Arrayy\tests;

use Arrayy\Collection\AbstractCollection;
use Arrayy\Collection\CollectionInterface;

/**
 * @phpstan-implements CollectionInterface<\stdClass>
 */
class StdClassCollection extends AbstractCollection implements CollectionInterface
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     */
    public function getType()
    {
        return \stdClass::class;
    }
}
