<?php

namespace Arrayy\Collection;

use Arrayy\Arrayy;

/**
 * A collection represents a group of objects, known as its elements.
 *
 * Some collections allow duplicate elements and others do not. Some are ordered
 * and others unordered.
 *
 * INFO: this collection thingy is inspired by https://github.com/ramsey/collection/
 */
interface CollectionInterface
{
    /**
     * @return static[]
     */
    public function getCollection(): array;

    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Merge current items and items of given collections into a new one.
     *
     * @param self ...$collections The collections to merge.
     *
     * @throws \InvalidArgumentException if any of the given collections are not of the same type
     *
     * @return self
     */
    public function merge(self ...$collections): self;

    /**
     * Assigns a value to the specified offset + check the type.
     *
     * @param int|string|null $offset
     * @param mixed           $value
     */
    public function offsetSet($offset, $value);

    /**
     * Prepend a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return Arrayy
     *                <p>(Mutable) Return this Arrayy object, with the prepended value.</p>
     */
    public function prepend($value, $key = null): Arrayy;

    /**
     * Append a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return Arrayy
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     */
    public function append($value, $key = null): Arrayy;

    /**
     * alias: for "append()"
     *
     * @param mixed $value
     *
     * @return Arrayy
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     *
     * @see Arrayy::append()
     */
    public function add($value): Arrayy;

    /**
     * Returns the values from given property or method.
     *
     * @param string $keyOrPropertyOrMethod the property or method name to filter by
     *
     * @throws \InvalidArgumentException if property or method is not defined
     *
     * @return array
     */
    public function column(string $keyOrPropertyOrMethod): array;

    /**
     * Returns a collection of matching items.
     *
     * @param string $keyOrPropertyOrMethod the property or method to evaluate
     * @param mixed  $value                 the value to match
     *
     * @throws \InvalidArgumentException if property or method is not defined
     *
     * @return self
     */
    public function where(string $keyOrPropertyOrMethod, $value): self;
}
