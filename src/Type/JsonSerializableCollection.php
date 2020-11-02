<?php

declare(strict_types=1);

namespace Arrayy\Type;

use Arrayy\Collection\Collection;

/**
 * @template T of \JsonSerializable
 * @extends Collection<array-key,T>
 */
class JsonSerializableCollection extends Collection implements TypeInterface
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     *
     * @phpstan-return class-string<\JsonSerializable>
     */
    public function getType()
    {
        return \JsonSerializable::class;
    }
}
