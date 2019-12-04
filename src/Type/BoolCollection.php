<?php

declare(strict_types=1);

namespace Arrayy\Type;

use Arrayy\Collection\Collection;

/**
 * @phpstan-extends Collection<bool>
 */
final class BoolCollection extends Collection implements TypeInterface
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     */
    public function getType()
    {
        return 'bool';
    }
}
