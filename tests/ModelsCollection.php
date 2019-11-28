<?php

namespace Arrayy\tests;

use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckInterface;

class ModelsCollection extends \Arrayy\Collection\AbstractCollection
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string|string[]|TypeCheckArray|TypeCheckInterface[]
     */
    public function getType()
    {
        return ModelInterface::class;
    }
}
