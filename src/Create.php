<?php

namespace Stringy;

if (!\function_exists('Stringy\create')) {
    /**
     * Creates a Stringy object and returns it on success.
     *
     * @param mixed  $str      Value to modify, after being cast to string
     * @param string $encoding The character encoding
     *
     * @throws \InvalidArgumentException if an array or object without a
     *                                   __toString method is passed as the first argument
     *
     * @return Stringy A Stringy object
     */
    function create($str, string $encoding = null)
    {
        return new Stringy($str, $encoding);
    }
}
