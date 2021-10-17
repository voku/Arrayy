<?php

namespace Arrayy\tests\Collection;

use Arrayy\tests\ModelInterface;

/**
 * @extends  \Arrayy\Collection\AbstractCollection<array-key,\Arrayy\tests\ModelInterface>
 */
class ModelsCollection extends \Arrayy\Collection\AbstractCollection
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * {@inheritdoc}
     */
    public function getType()
    {
        return ModelInterface::class;
    }
}
