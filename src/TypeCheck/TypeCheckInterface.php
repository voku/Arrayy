<?php

namespace Arrayy\TypeCheck;

/**
 * inspired by https://github.com/spatie/value-object
 *
 * @internal
 */
interface TypeCheckInterface
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function checkType(&$value): bool;

    /**
     * @return string[]
     */
    public function getTypes(): array;

    /**
     * @param string $expectedTypes
     * @param mixed  $value
     * @param string $type
     *
     * @return \Throwable
     */
    public function throwException($expectedTypes, $value, $type): \Throwable;
}
