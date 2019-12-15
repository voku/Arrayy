<?php

namespace Arrayy\tests\Collection;

use Arrayy\Collection\AbstractCollection;

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
