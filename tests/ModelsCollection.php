<?php

namespace Arrayy\tests;

use Arrayy\Collection\AbstractCollection;

class ModelsCollection extends AbstractCollection
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     */
    public function getType(): string
    {
        return ModelInterface::class;
    }
}
