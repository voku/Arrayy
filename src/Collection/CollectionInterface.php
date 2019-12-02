<?php

namespace Arrayy\Collection;

use Arrayy\Arrayy;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckInterface;

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
     * alias: for "append()"
     *
     * @param mixed $value
     *
     * @return Arrayy
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     *
     * @see          CollectionInterface::append()
     *
     * @psalm-param  T $value
     * @psalm-return Arrayy<TKey,T>
     */
    public function add($value): Arrayy;

    /**
     * Append a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return Arrayy
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     *
     * @psalm-param  T $value
     * @psalm-return Arrayy<TKey,T>
     */
    public function append($value, $key = null): Arrayy;

    /**
     * Clears the current collection, by removing all elements.
     */
    public function clear();

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
     * Checks whether an element is contained in the collection.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $value
     *                         <p>The element to search for.</p>
     * @param bool  $recursive
     * @param bool  $strict
     *
     * @return bool
     *              <p>TRUE if the collection contains the element, FALSE otherwise.</p>
     *
     * @psalm-param T $value
     */
    public function contains($value, bool $recursive = false, bool $strict = true): bool;

    /**
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param int|string $key
     *                        <p>The key/index to check for.</p>
     *
     * @return bool
     *              <p>TRUE if the collection contains an element with the specified key/index,
     *              FALSE otherwise.</p>
     *
     * @psalm-param TKey $key
     */
    public function containsKey($key): bool;

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return mixed
     *
     * @psalm-return T|false
     */
    public function current();

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param \Closure $closure the predicate
     *
     * @return bool
     *              <p>TRUE if the predicate is TRUE for at least one element, FALSE otherwise.</p>
     *
     * @psalm-param Closure(TKey=, T=):bool $closure
     */
    public function exists(\Closure $closure): bool;

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @param \Closure $closure the predicate used for filtering
     * @param int      $flag    [optional]
     *
     * @return Arrayy
     *                <p>A collection with the results of the filter operation.</p>
     *
     * @psalm-param  Closure(T=, TKey=):bool $closure
     * @psalm-return Collection<TKey, T>
     */
    public function filter($closure = null, int $flag = \ARRAY_FILTER_USE_BOTH): Arrayy;

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     *
     * @psalm-return T|false
     */
    public function first();

    /**
     * Tests whether the given predicate p holds for all elements of this collection.
     *
     * @param \Closure $p the predicate
     *
     * @return bool TRUE, if the predicate yields TRUE for all elements, FALSE otherwise
     *
     * @psalm-param Closure(TKey=, T=):bool $p
     */
    public function forAll(\Closure $p): bool;

    /**
     * Gets the element at the specified key/index.
     *
     * @param int|string $key
     *                        <p>The key/index of the element to retrieve.</p>
     *
     * @return mixed
     *
     * @psalm-param  TKey $key
     * @psalm-return T|null
     */
    public function get($key);

    /**
     * @return static[]
     */
    public function getCollection(): array;

    /**
     * Gets all keys/indices of the collection.
     *
     * @return Arrayy
     *                <p>The keys/indices of the collection, in the order of the corresponding
     *                elements in the collection.</p>
     *
     * @psalm-return TKey[]
     */
    public function getKeys(): Arrayy;

    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string|string[]|TypeCheckArray|TypeCheckInterface[]
     */
    public function getType();

    /**
     * Gets all values of the collection.
     *
     * @return Arrayy
     *                <p>The values of all elements in the collection, in the order they
     *                appear in the collection.</p>
     *
     * @psalm-return T[]
     */
    public function getValues(): Arrayy;

    /**
     * Gets the index/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element the element to search for
     *
     * @return bool|int|string the key/index of the element or FALSE if the element was not found
     *
     * @psalm-param  T $element
     * @psalm-return TKey|false
     */
    public function indexOf($element);

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @param int|int[]|string|string[]|null $keys
     *
     * @return bool
     *              <p>TRUE if the collection is empty, FALSE otherwise.</p>
     */
    public function isEmpty($keys = null): bool;

    /**
     * Gets the key/index of the element at the current iterator position.
     *
     * @return int|string|null
     *
     * @psalm-return TKey|null
     */
    public function key();

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     *
     * @psalm-return T|false
     */
    public function last();

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param callable $callable
     * @param bool     $useKeyAsSecondParameter
     * @param mixed    ...$arguments
     *
     * @return Arrayy
     *
     * @psalm-template U
     * @psalm-param    Closure(T=):U $func
     * @psalm-return   Collection<TKey, U>
     */
    public function map(callable $callable, bool $useKeyAsSecondParameter = false, ...$arguments): Arrayy;

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
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return mixed
     *
     * @psalm-return T|false
     */
    public function next();

    /**
     * Assigns a value to the specified offset + check the type.
     *
     * @param int|string|null $offset
     * @param mixed           $value
     */
    public function offsetSet($offset, $value);

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param \Closure $p the predicate on which to partition
     *
     * @return array<int, Collection> An array with two elements. The first element contains the collection
     *                    of elements where the predicate returned TRUE, the second element
     *                    contains the collection of elements where the predicate returned FALSE.
     *
     * @psalm-param  Closure(TKey=, T=):bool $p
     * @psalm-return array{0: Collection<TKey, T>, 1: Collection<TKey, T>}
     */
    public function partition(\Closure $p): array;

    /**
     * Prepend a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return Arrayy
     *                <p>(Mutable) Return this Arrayy object, with the prepended value.</p>
     *
     * @psalm-param  T $element
     * @psalm-return Arrayy<TKey,T>
     */
    public function prepend($value, $key = null): Arrayy;

    /**
     * Removes the element at the specified index from the collection.
     *
     * Remove a value from the current array (optional using dot-notation).
     *
     * @param mixed $key
     *
     * @return Arrayy
     *
     * @psalm-param  TKey $key
     * @psalm-return Arrayy<TKey,T>
     */
    public function remove($key): Arrayy;

    /**
     * Removes the specified element from the collection, if it is found.
     *
     * @param mixed $element
     *                       <p>The element to remove.</p>
     *
     * @return Arrayy
     *
     * @psalm-param  T $element
     * @psalm-return Arrayy<TKey,T>
     */
    public function removeElement($element): Arrayy;

    /**
     * Sets an element in the collection at the specified key/index.
     *
     * @param int|string $key
     *                          <p>The key/index of the element to set.</p>
     * @param mixed      $value
     *                          <p>The element to set.<p>
     *
     * @psalm-param TKey $key
     * @psalm-param T $value
     */
    public function set($key, $value);

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     *
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Keys have to be preserved by this method. Calling this method will only return the
     * selected slice and NOT change the elements contained in the collection slice is called on.
     *
     * @param int      $offset       the offset to start from
     * @param int|null $length       the maximum number of elements to return, or null for no limit
     * @param bool     $preserveKeys
     *
     * @return Arrayy
     *
     * @psalm-return Arrayy<TKey,T>
     */
    public function slice(int $offset, int $length = null, bool $preserveKeys = false): Arrayy;

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     *
     * @psalm-return array<TKey,T>
     */
    public function toArray(): array;

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
