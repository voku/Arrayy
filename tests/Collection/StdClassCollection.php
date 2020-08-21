<?php

namespace Arrayy\tests\Collection;

use Arrayy\Collection\AbstractCollection;

/**
 * @template TKey of array-key
 * @template T
 * @extends  \Arrayy\Collection\AbstractCollection<TKey,\stdClass>
 */
class StdClassCollection extends AbstractCollection
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
