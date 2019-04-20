<?php

declare(strict_types=1);

namespace Arrayy;

use Arrayy\Collection\Collection;
use Arrayy\Collection\CollectionInterface;
use Arrayy\Collection\CollectionSetTypeInterface;

if (!\function_exists('Arrayy\create')) {
    /**
     * Creates a Arrayy object.
     *
     * @param mixed $data
     *
     * @return Arrayy
     */
    function create($data): Arrayy
    {
        return new Arrayy($data);
    }

    /**
     * Creates a Collection object.
     *
     * @param string $type
     * @param mixed  $data
     *
     * @return CollectionInterface
     */
    function collection($type, $data = []): CollectionInterface
    {
        return new Collection($type, $data);
    }
}
