<?php

declare(strict_types=1);

namespace Arrayy;

if (!\function_exists('Arrayy\create')) {
    /**
     * Creates a Arrayy object
     *
     * @param mixed $array
     *
     * @return Arrayy
     */
    function create($array)
    {
        return new Arrayy($array);
    }
}
