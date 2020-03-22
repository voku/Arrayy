<?php

namespace Arrayy\tests\Collection;

use Arrayy\Collection\AbstractCollection;

class UserDataCollection extends AbstractCollection
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     */
    public function getType()
    {
        return \Arrayy\tests\UserData::class;
    }
}
