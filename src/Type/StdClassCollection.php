<?php

declare(strict_types=1);

namespace Arrayy\Type;

use Arrayy\Collection\Collection;

/**
 * @template T of \stdClass
 * @extends Collection<T>
 */
class StdClassCollection extends Collection implements TypeInterface
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     *
     * @psalm-return class-string<T>
     */
    public function getType()
    {
        return \stdClass::class;
    }
}
