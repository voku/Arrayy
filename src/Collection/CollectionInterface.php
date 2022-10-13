<?php

namespace Arrayy\Collection;

use Arrayy\ArrayyIterator;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckInterface;

/**
 * A collection represents a group of objects, known as its elements.
 *
 * Some collections allow duplicate elements and others do not. Some are ordered
 * and others unordered.
 *
 * INFO: this collection thingy is inspired by https://github.com/ramsey/collection/
 *
 * @template TKey of array-key
 * @template T
 * @extends \IteratorAggregate<TKey,T>
 * @extends \ArrayAccess<TKey,T>
 */
interface CollectionInterface extends \IteratorAggregate, \ArrayAccess, \Serializable, \JsonSerializable, \Countable
{
    /**
     * Assigns a value to the specified element.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     *
     * @phpstan-param TKey $key
     * @phpstan-param T $value
     */
    public function __set($key, $value);

    /**
     * alias: for "CollectionInterface->append()"
     *
     * @param mixed $value
     *
     * @return CollectionInterface
     *                             <p>(Mutable) Return this CollectionInterface object, with the appended values.</p>
     *
     * @see          CollectionInterface::append()
     *
     * @phpstan-param  T $value
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function add($value);

    /**
     * Append a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return CollectionInterface
     *                             <p>(Mutable) Return this CollectionInterface object, with the appended values.</p>
     *
     * @phpstan-param T $value
     * @phpstan-param TKey|null $key
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function append($value, $key = null);

    /**
     * Append a (key) + values to the current array.
     *
     * @param array $values
     * @param mixed $key
     *
     * @return CollectionInterface
     *                             <p>(Mutable) Return this CollectionInterface object, with the appended values.</p>
     *
     * @phpstan-param array<T> $values
     * @phpstan-param TKey $key
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function appendArrayValues(array $values, $key = null);

    /**
     * Clears the current collection, by removing all elements.
     *
     * @return void
     */
    public function clear();

    /**
     * Returns the values from given property or method.
     *
     * @param string $keyOrPropertyOrMethod the property or method name to filter by
     *
     * @throws \InvalidArgumentException if property or method is not defined
     *
     * @return array<mixed>
     */
    public function column(string $keyOrPropertyOrMethod): array;

    /**
     * Check if an item is in the current array.
     *
     * @param float|int|string $value
     * @param bool             $recursive
     * @param bool             $strict
     *
     * @return bool
     *
     * @phpstan-param T $value
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
     * @phpstan-param TKey $key
     */
    public function containsKey($key): bool;

    /**
     * alias: for "CollectionInterface->contains()"
     *
     * @param float|int|string $value
     *
     * @return bool
     *
     * @see         CollectionInterface::contains()
     *
     * @phpstan-param T $value
     */
    public function containsValue($value): bool;

    /**
     * alias: for "CollectionInterface->contains($value, true)"
     *
     * @param float|int|string $value
     *
     * @return bool
     *
     * @see         CollectionInterface::contains()
     *
     * @phpstan-param T $value
     */
    public function containsValueRecursive($value): bool;

    /**
     * Creates an CollectionInterface object.
     *
     * @param mixed  $data
     * @param string $iteratorClass
     * @param bool   $checkPropertiesInConstructor
     *
     * @return CollectionInterface
     *                             <p>(Immutable) Returns an new instance of the CollectionInterface object.</p>
     *
     * @template TKeyCreate as TKey
     * @template TCreate as T
     * @phpstan-param array<TKeyCreate,TCreate> $data
     * @phpstan-param  class-string<\Arrayy\ArrayyIterator> $iteratorClass
     * @phpstan-return static<TKeyCreate,TCreate>
     *
     * @psalm-mutation-free
     */
    public static function create(
        $data = [],
        string $iteratorClass = ArrayyIterator::class,
        bool $checkPropertiesInConstructor = true
    );

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return false|mixed
     *
     * @phpstan-return T|false
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
     * @phpstan-param \Closure(T,TKey):bool $closure
     */
    public function exists(\Closure $closure): bool;

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @param \Closure $closure the predicate used for filtering
     * @param int      $flag    [optional]
     *
     * @return CollectionInterface
     *                             <p>A collection with the results of the filter operation.</p>
     *
     * @phpstan-param \Closure(T,TKey):bool $closure
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function filter($closure = null, int $flag = \ARRAY_FILTER_USE_BOTH);

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     *
     * @phpstan-return T|false
     */
    public function first();

    /**
     * Tests whether the given closure retrun something valid for all elements of this array.
     *
     * @param \Closure $closure the predicate
     *
     * @return bool TRUE, if the predicate yields TRUE for all elements, FALSE otherwise
     *
     * @phpstan-param \Closure(T,TKey):bool $closure
     */
    public function validate(\Closure $closure): bool;

    /**
     * Gets the element at the specified key/index.
     *
     * @param int|string $key
     *                        <p>The key/index of the element to retrieve.</p>
     *
     * @return mixed
     *
     * @phpstan-param TKey $key
     * @phpstan-return T|null
     */
    public function get($key);

    /**
     * Creates a copy of the CollectionInterface.
     *
     * @return array
     *
     * @phpstan-return array<T>
     */
    public function getArrayCopy(): array;

    /**
     * @return array
     *
     * @phpstan-return array<T>
     */
    public function getCollection(): array;

    /**
     * Gets all keys/indices of the collection.
     *
     * @return CollectionInterface
     *
     * @phpstan-return TKey[]
     */
    public function getKeys();

    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string|string[]|TypeCheckArray|TypeCheckInterface[]
     *
     * @phpstan-return string|string[]|class-string|class-string[]|TypeCheckArray<array-key,TypeCheckInterface>|TypeCheckInterface[]
     */
    public function getType();

    /**
     * Gets all values of the collection.
     *
     * @return CollectionInterface
     *
     * @phpstan-return T[]
     */
    public function getValues();

    /**
     * Check if an array has a given value.
     *
     * INFO: if you need to search recursive please use ```contains()```
     *
     * @param mixed $value
     *
     * @return bool
     *
     * @phpstan-param T $value
     */
    public function hasValue($value): bool;

    /**
     * Gets the index/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element the element to search for
     *
     * @return false|mixed the key/index of the element or FALSE if the element was not found
     *
     * @phpstan-param T $element
     * @phpstan-return TKey|false
     */
    public function indexOf($element);

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @param int|int[]|string|string[]|null $keys
     *
     * @return bool
     *              <p>TRUE if the collection is empty, FALSE otherwise.</p>
     *
     * @phpstan-param TKey|TKey[]|null $keys
     */
    public function isEmpty($keys = null): bool;

    /**
     * Gets the key/index of the element at the current iterator position.
     *
     * @return int|string|null
     *
     * @phpstan-return TKey|null
     */
    public function key();

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     *
     * @phpstan-return T|false
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
     * @return CollectionInterface
     *
     * @phpstan-param callable(T,TKey,mixed):mixed $callable
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function map(callable $callable, bool $useKeyAsSecondParameter = false, ...$arguments);

    /**
     * Merge current items and items of given collections into a new one.
     *
     * @param CollectionInterface ...$collections The collections to merge.
     *
     * @throws \InvalidArgumentException if any of the given collections are not of the same type
     *
     * @return CollectionInterface
     *
     * @phpstan-param CollectionInterface<TKey,T> ...$collections
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function merge(self ...$collections);

    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return mixed
     *
     * @phpstan-return T|false
     */
    public function next();

    /**
     * Assigns a value to the specified offset + check the type.
     *
     * @param int|string|null $offset
     * @param mixed           $value
     *
     * @return void
     *
     * @phpstan-param TKey $offset
     * @phpstan-param T $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value);

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param \Closure $p the predicate on which to partition
     *
     * @return array<int, CollectionInterface> An array with two elements. The first element contains the collection
     *                    of elements where the predicate returned TRUE, the second element
     *                    contains the collection of elements where the predicate returned FALSE.
     *
     * @phpstan-param \Closure(T,TKey):bool $p
     * @phpstan-return array{0: CollectionInterface<TKey,T>, 1: CollectionInterface<TKey,T>}
     */
    public function partition(\Closure $p): array;

    /**
     * Prepend a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return CollectionInterface
     *                             <p>(Mutable) Return this CollectionInterface object, with the prepended value.</p>
     *
     * @phpstan-param T $value
     * @phpstan-param TKey|null $key
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function prepend($value, $key = null);

    /**
     * Removes the element at the specified index from the collection.
     *
     * Remove a value from the current array (optional using dot-notation).
     *
     * @param mixed $key
     *
     * @return CollectionInterface
     *
     * @phpstan-param TKey $key
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function remove($key);

    /**
     * Removes the specified element from the collection, if it is found.
     *
     * @param mixed $element
     *                       <p>The element to remove.</p>
     *
     * @return CollectionInterface
     *
     * @phpstan-param  T $element
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function removeElement($element);

    /**
     * Removes a particular value from an array (numeric or associative).
     *
     * @param mixed $value
     *
     * @return CollectionInterface
     *                             <p>(Immutable)</p>
     *
     * @phpstan-param T $value
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function removeValue($value);

    /**
     * Sets an element in the collection at the specified key/index.
     *
     * @param int|string $key
     *                          <p>The key/index of the element to set.</p>
     * @param mixed      $value
     *                          <p>The element to set.<p>
     *
     * @return CollectionInterface
     *
     * @phpstan-param TKey $key
     * @phpstan-param T $value
     * @phpstan-return CollectionInterface<TKey,T>
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
     * @return CollectionInterface
     *
     * @phpstan-return CollectionInterface<array-key|TKey,T>
     */
    public function slice(int $offset, int $length = null, bool $preserveKeys = false);

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     *
     * @phpstan-return array<TKey,T>
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
     * @return CollectionInterface
     *
     * @phpstan-return CollectionInterface<TKey,T>
     */
    public function where(string $keyOrPropertyOrMethod, $value);
}
