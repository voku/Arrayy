<?php

namespace Arrayy\tests;

use Arrayy\Collection\Collection;

class StdBaseClassCollection extends Collection
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     */
    public function getType(): string
    {
        return \stdClass::class;
    }
}
