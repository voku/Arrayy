<?php

namespace Arrayy\tests\Collection;

use Arrayy\tests\ModelInterface;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckInterface;

class ModelsCollection extends \Arrayy\Collection\AbstractCollection
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string|string[]|TypeCheckArray|TypeCheckInterface[]
     *
     * @psalm-return string|string[]|class-string|class-string[]|TypeCheckArray<array-key,TypeCheckInterface>|TypeCheckInterface[]
     */
    public function getType()
    {
        return ModelInterface::class;
    }
}
