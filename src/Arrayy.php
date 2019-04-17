<?php

declare(strict_types=1);

namespace Arrayy;

/** @noinspection ClassReImplementsParentInterfaceInspection */
/** @noinspection PhpComposerExtensionStubsInspection */

/**
 * Methods to manage arrays.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Arrayy extends \ArrayObject implements \IteratorAggregate, \ArrayAccess, \Serializable, \JsonSerializable, \Countable
{
    /**
     * @var array
     */
    protected $array = [];

    /**
     * @var ArrayyRewindableGenerator|null
     */
    protected $generator;

    /**
     * @var string
     */
    protected $iteratorClass = ArrayyIterator::class;

    /**
     * @var string
     */
    protected $pathSeparator = '.';

    /**
     * @var bool
     */
    protected $checkPropertyTypes = false;

    /**
     * @var bool
     */
    protected $checkForMissingPropertiesInConstructor = false;

    /**
     * @var bool
     */
    protected $checkPropertiesMismatchInConstructor = false;

    /**
     * @var array|Property[]
     */
    protected $properties = [];

    /** @noinspection MagicMethodsValidityInspection */
    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Initializes
     *
     * @param mixed  $array                                  <p>
     *                                                       Should be an array or a generator, otherwise it will try
     *                                                       to convert it into an array.
     *                                                       </p>
     * @param string $iteratorClass                          optional <p>
     *                                                       You can overwrite the ArrayyIterator, but mostly you don't
     *                                                       need this option.
     *                                                       </p>
     * @param bool   $checkForMissingPropertiesInConstructor optional <p>
     *                                                       You need to extend the "Arrayy"-class and you need to set
     *                                                       the $checkPropertiesMismatchInConstructor class property
     *                                                       to
     *                                                       true, otherwise this option didn't not work anyway.
     *                                                       </p>
     */
    public function __construct(
        $array = [],
        string $iteratorClass = ArrayyIterator::class,
        bool $checkForMissingPropertiesInConstructor = true
    ) {
        $array = $this->fallbackForArray($array);

        // used only for serialize + unserialize, all other methods are overwritten
        parent::__construct([], 0, $iteratorClass);

        $checkForMissingPropertiesInConstructor = $this->checkForMissingPropertiesInConstructor === true
                                                  &&
                                                  $checkForMissingPropertiesInConstructor === true;

        if (
            $this->checkPropertyTypes === true
            ||
            $checkForMissingPropertiesInConstructor === true
        ) {
            $this->properties = $this->getPropertiesFromPhpDoc();
        }

        if (
            $this->checkPropertiesMismatchInConstructor === true
            &&
            \count($array) !== 0
            &&
            \count(\array_diff_key($this->properties, $array)) > 0
        ) {
            throw new \InvalidArgumentException('Property mismatch - input: ' . \print_r(\array_keys($array), true) . ' | expected: ' . \print_r(\array_keys($this->properties), true));
        }

        foreach ($array as $key => &$value) {
            $this->internalSet(
                $key,
                $value,
                $checkForMissingPropertiesInConstructor
            );
        }

        $this->setIteratorClass($iteratorClass);
    }

    /**
     * Call object as function.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function __invoke($key = null)
    {
        if ($key !== null) {
            $this->generatorToArray();

            return $this->array[$key] ?? false;
        }

        return $this->getArray();
    }

    /**
     * Whether or not an element exists by key.
     *
     * @param mixed $key
     *
     * @return bool
     *              <p>True is the key/index exists, otherwise false.</p>
     */
    public function __isset($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Assigns a value to the specified element.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->internalSet($key, $value);
    }

    /**
     * magic to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Unset element by key.
     *
     * @param mixed $key
     */
    public function __unset($key)
    {
        $this->internalRemove($key);
    }

    /**
     * Get a value by key.
     *
     * @param mixed $key
     *
     * @return mixed
     *               <p>Get a Value from the current array.</p>
     */
    public function &__get($key)
    {
        $return = $this->get($key);

        if (\is_array($return)) {
            return static::create($return, $this->iteratorClass, false);
        }

        return $return;
    }

    /**
     * alias: for "Arrayy->append()"
     *
     * @param mixed $value
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     *
     * @see Arrayy::append()
     */
    public function add($value): self
    {
        return $this->append($value);
    }

    /**
     * Append a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     */
    public function append($value, $key = null): self
    {
        $this->generatorToArray();

        if ($key !== null) {
            if (
                isset($this->array[$key])
                &&
                \is_array($this->array[$key])
            ) {
                $this->array[$key][] = $value;
            } else {
                $this->array[$key] = $value;
            }
        } else {
            $this->array[] = $value;
        }

        return $this;
    }

    /**
     * Sort the entries by value.
     *
     * @param int $sort_flags [optional] <p>
     *                        You may modify the behavior of the sort using the optional
     *                        parameter sort_flags, for details
     *                        see sort.
     *                        </p>
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function asort(int $sort_flags = 0): self
    {
        $this->generatorToArray();

        \asort($this->array, $sort_flags);

        return $this;
    }

    /**
     * Counts all elements in an array, or something in an object.
     *
     * <p>
     * For objects, if you have SPL installed, you can hook into count() by implementing interface {@see Countable}.
     * The interface has exactly one method, {@see Countable::count()}, which returns the return value for the count()
     * function. Please see the {@see Array} section of the manual for a detailed explanation of how arrays are
     * implemented and used in PHP.
     * </p>
     *
     * @see http://php.net/manual/en/function.count.php
     *
     * @param int $mode [optional] If the optional mode parameter is set to
     *                  COUNT_RECURSIVE (or 1), count
     *                  will recursively count the array. This is particularly useful for
     *                  counting all the elements of a multidimensional array. count does not detect infinite recursion.
     *
     * @return int
     *             <p>
     *             The number of elements in var, which is
     *             typically an array, since anything else will have one
     *             element.
     *             </p>
     *             <p>
     *             If var is not an array or an object with
     *             implemented Countable interface,
     *             1 will be returned.
     *             There is one exception, if var is &null;,
     *             0 will be returned.
     *             </p>
     *             <p>
     *             Caution: count may return 0 for a variable that isn't set,
     *             but it may also return 0 for a variable that has been initialized with an
     *             empty array. Use isset to test if a variable is set.
     *             </p>
     */
    public function count(int $mode = \COUNT_NORMAL): int
    {
        return \count($this->getArray(), $mode);
    }

    /**
     * Exchange the array for another one.
     *
     * @param array|static $data
     *
     * @return array
     */
    public function exchangeArray($data): array
    {
        $this->array = $this->fallbackForArray($data);

        return $this->array;
    }

    /**
     * Creates a copy of the ArrayyObject.
     *
     * @return array
     */
    public function getArrayCopy(): array
    {
        $this->generatorToArray();

        return $this->array;
    }

    /**
     * Returns a new ArrayyIterator, thus implementing the \ArrayIterator interface.
     *
     * @return \ArrayIterator
     *                        <p>An iterator for the values in the array.</p>
     */
    public function getIterator(): \ArrayIterator
    {
        $iterator = $this->getIteratorClass();

        return new $iterator($this->getArray(), 0, static::class);
    }

    /**
     * Gets the iterator classname for the ArrayObject.
     *
     * @return string
     */
    public function getIteratorClass(): string
    {
        return $this->iteratorClass;
    }

    /**
     * Sort the entries by key
     *
     * @param int $sort_flags [optional] <p>
     *                        You may modify the behavior of the sort using the optional
     *                        parameter sort_flags, for details
     *                        see sort.
     *                        </p>
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function ksort(int $sort_flags = 0): self
    {
        $this->generatorToArray();

        \ksort($this->array, $sort_flags);

        return $this;
    }

    /**
     * Sort an array using a case insensitive "natural order" algorithm
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function natcasesort(): self
    {
        $this->generatorToArray();

        \natcasesort($this->array);

        return $this;
    }

    /**
     * Sort entries using a "natural order" algorithm
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function natsort(): self
    {
        $this->generatorToArray();

        \natsort($this->array);

        return $this;
    }

    /**
     * Whether or not an offset exists.
     *
     * @param bool|float|int|string $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        $this->generatorToArray();

        if ($this->isEmpty()) {
            return false;
        }

        // php cast "bool"-index into "int"-index
        if ((bool) $offset === $offset) {
            $offset = (int) $offset;
        }

        $tmpReturn = \array_key_exists($offset, $this->array);

        if (
            $tmpReturn === true
            ||
            (
                $tmpReturn === false
                &&
                \strpos((string) $offset, $this->pathSeparator) === false
            )
        ) {
            return $tmpReturn;
        }

        $offsetExists = false;

        if (\strpos((string) $offset, $this->pathSeparator) !== false) {
            $offsetExists = false;
            $explodedPath = \explode($this->pathSeparator, (string) $offset);
            $lastOffset = \array_pop($explodedPath);
            $containerPath = \implode($this->pathSeparator, $explodedPath);

            $this->callAtPath(
                $containerPath,
                static function ($container) use ($lastOffset, &$offsetExists) {
                    $offsetExists = \array_key_exists($lastOffset, $container);
                }
            );
        }

        return $offsetExists;
    }

    /**
     * Returns the value at specified offset.
     *
     * @param float|int|string $offset
     *
     * @return mixed
     *               <p>Will return null if the offset did not exists.</p>
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->get($offset) : null;
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param int|string|null $offset
     * @param mixed           $value
     */
    public function offsetSet($offset, $value)
    {
        $this->generatorToArray();

        if ($offset === null) {
            $this->array[] = $value;
        } else {
            $this->internalSet($offset, $value);
        }
    }

    /**
     * Unset an offset.
     *
     * @param float|int|string $offset
     */
    public function offsetUnset($offset)
    {
        $this->generatorToArray();

        if ($this->isEmpty()) {
            return;
        }

        if (\array_key_exists($offset, $this->array)) {
            unset($this->array[$offset]);

            return;
        }

        if (\strpos((string) $offset, $this->pathSeparator) !== false) {
            $path = \explode($this->pathSeparator, (string) $offset);
            $pathToUnset = \array_pop($path);

            $this->callAtPath(
                \implode($this->pathSeparator, $path),
                static function (&$offset) use ($pathToUnset) {
                    unset($offset[$pathToUnset]);
                }
            );
        }
    }

    /** @noinspection SenselessProxyMethodInspection | can not add return type, because of the "Serializable" interface */

    /**
     * Serialize the current "Arrayy"-object.
     *
     * @return string
     */
    public function serialize(): string
    {
        $this->generatorToArray();

        return parent::serialize();
    }

    /**
     * Sets the iterator classname for the current "Arrayy"-object.
     *
     * @param string $class
     *
     * @throws \InvalidArgumentException
     */
    public function setIteratorClass($class)
    {
        if (\class_exists($class)) {
            $this->iteratorClass = $class;

            return;
        }

        if (\strpos($class, '\\') === 0) {
            $class = '\\' . $class;
            if (\class_exists($class)) {
                $this->iteratorClass = $class;

                return;
            }
        }

        throw new \InvalidArgumentException('The iterator class does not exist: ' . $class);
    }

    /**
     * Sort the entries with a user-defined comparison function and maintain key association.
     *
     * @param \callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function uasort($function): self
    {
        if (!\is_callable($function)) {
            throw new \InvalidArgumentException(
                'Passed function must be callable'
            );
        }

        $this->generatorToArray();

        \uasort($this->array, $function);

        return $this;
    }

    /**
     * Sort the entries by keys using a user-defined comparison function.
     *
     * @param \callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function uksort($function): self
    {
        return $this->customSortKeys($function);
    }

    /**
     * Unserialize an string and return the instance of the "Arrayy"-class.
     *
     * @param string $string
     *
     * @return static
     */
    public function unserialize($string): self
    {
        parent::unserialize($string);

        return $this;
    }

    /**
     * Append a (key) + values to the current array.
     *
     * @param array $values
     * @param mixed $key
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     */
    public function appendArrayValues(array $values, $key = null): self
    {
        $this->generatorToArray();

        if ($key !== null) {
            if (
                isset($this->array[$key])
                &&
                \is_array($this->array[$key])
            ) {
                foreach ($values as $value) {
                    $this->array[$key][] = $value;
                }
            } else {
                foreach ($values as $value) {
                    $this->array[$key] = $value;
                }
            }
        } else {
            foreach ($values as $value) {
                $this->array[] = $value;
            }
        }

        return $this;
    }

    /**
     * Add a suffix to each key.
     *
     * @param mixed $prefix
     *
     * @return static
     *                <p>(Immutable) Return an Arrayy object, with the prefixed keys.</p>
     */
    public function appendToEachKey($prefix): self
    {
        // init
        $result = [];

        foreach ($this->getGenerator() as $key => $item) {
            if ($item instanceof self) {
                $result[$prefix . $key] = $item->appendToEachKey($prefix);
            } elseif (\is_array($item)) {
                $result[$prefix . $key] = self::create($item, $this->iteratorClass, false)
                    ->appendToEachKey($prefix)
                    ->toArray();
            } else {
                $result[$prefix . $key] = $item;
            }
        }

        return self::create($result, $this->iteratorClass, false);
    }

    /**
     * Add a prefix to each value.
     *
     * @param mixed $prefix
     *
     * @return static
     *                <p>(Immutable) Return an Arrayy object, with the prefixed values.</p>
     */
    public function appendToEachValue($prefix): self
    {
        // init
        $result = [];

        foreach ($this->getGenerator() as $key => $item) {
            if ($item instanceof self) {
                $result[$key] = $item->appendToEachValue($prefix);
            } elseif (\is_array($item)) {
                $result[$key] = self::create($item, $this->iteratorClass, false)->appendToEachValue($prefix)->toArray();
            } elseif (\is_object($item)) {
                $result[$key] = $item;
            } else {
                $result[$key] = $prefix . $item;
            }
        }

        return self::create($result, $this->iteratorClass, false);
    }

    /**
     * Sort an array in reverse order and maintain index association.
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function arsort(): self
    {
        $this->generatorToArray();

        \arsort($this->array);

        return $this;
    }

    /**
     * Iterate over the current array and execute a callback for each loop.
     *
     * @param \Closure $closure
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function at(\Closure $closure): self
    {
        $arrayy = clone $this;

        foreach ($arrayy->getGenerator() as $key => $value) {
            $closure($value, $key);
        }

        return static::create(
            $arrayy->toArray(),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Returns the average value of the current array.
     *
     * @param int $decimals <p>The number of decimal-numbers to return.</p>
     *
     * @return float|int
     *                   <p>The average value.</p>
     */
    public function average($decimals = 0)
    {
        $count = \count($this->getArray(), \COUNT_NORMAL);

        if (!$count) {
            return 0;
        }

        if (!\is_int($decimals)) {
            $decimals = 0;
        }

        return \round(\array_sum($this->getArray()) / $count, $decimals);
    }

    /**
     * Changes all keys in an array.
     *
     * @param int $case [optional] <p> Either <strong>CASE_UPPER</strong><br />
     *                  or <strong>CASE_LOWER</strong> (default)</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function changeKeyCase(int $case = \CASE_LOWER): self
    {
        if (
            $case !== \CASE_LOWER
            &&
            $case !== \CASE_UPPER
        ) {
            $case = \CASE_LOWER;
        }

        $return = [];
        foreach ($this->getGenerator() as $key => $value) {
            if ($case === \CASE_LOWER) {
                /** @noinspection PhpComposerExtensionStubsInspection */
                $key = \mb_strtolower((string) $key);
            } else {
                /** @noinspection PhpComposerExtensionStubsInspection */
                $key = \mb_strtoupper((string) $key);
            }

            $return[$key] = $value;
        }

        return static::create(
            $return,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Change the path separator of the array wrapper.
     *
     * By default, the separator is: "."
     *
     * @param string $separator <p>Separator to set.</p>
     *
     * @return static
     *                <p>Mutable</p>
     */
    public function changeSeparator($separator): self
    {
        $this->pathSeparator = $separator;

        return $this;
    }

    /**
     * Create a chunked version of the current array.
     *
     * @param int  $size         <p>Size of each chunk.</p>
     * @param bool $preserveKeys <p>Whether array keys are preserved or no.</p>
     *
     * @return static
     *                <p>(Immutable) A new array of chunks from the original array.</p>
     */
    public function chunk($size, $preserveKeys = false): self
    {
        return static::create(
            \array_chunk($this->getArray(), $size, $preserveKeys),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Clean all falsy values from the current array.
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function clean(): self
    {
        return $this->filter(
            static function ($value) {
                return (bool) $value;
            }
        );
    }

    /**
     * WARNING!!! -> Clear the current array.
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with an empty array.</p>
     */
    public function clear(): self
    {
        $this->array = [];
        $this->generator = null;

        return $this;
    }

    /**
     * Check if an item is in the current array.
     *
     * @param float|int|string $value
     * @param bool             $recursive
     * @param bool             $strict
     *
     * @return bool
     */
    public function contains($value, bool $recursive = false, bool $strict = true): bool
    {
        if ($recursive === true) {
            return $this->in_array_recursive($value, $this->getArray(), $strict);
        }

        return \in_array($value, $this->getArray(), $strict);
    }

    /**
     * Check if an (case-insensitive) string is in the current array.
     *
     * @param string $value
     * @param bool   $recursive
     *
     * @return bool
     */
    public function containsCaseInsensitive($value, $recursive = false): bool
    {
        if ($recursive === true) {
            /** @noinspection PhpComposerExtensionStubsInspection */
            return $this->in_array_recursive(
                \mb_strtoupper((string) $value),
                $this->walk(
                    static function (&$val) {
                        /** @noinspection PhpComposerExtensionStubsInspection */
                        $val = \mb_strtoupper((string) $val);
                    },
                    true
                )->getArray(),
                true
            );
        }

        /** @noinspection PhpComposerExtensionStubsInspection */
        return \in_array(
            \mb_strtoupper((string) $value),
            $this->walk(
                static function (&$val) {
                    /** @noinspection PhpComposerExtensionStubsInspection */
                    $val = \mb_strtoupper((string) $val);
                },
                false
            )->getArray(),
            true
        );
    }

    /**
     * Check if the given key/index exists in the array.
     *
     * @param float|int|string $key <p>key/index to search for</p>
     *
     * @return bool
     *              <p>Returns true if the given key/index exists in the array, false otherwise.</p>
     */
    public function containsKey($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Check if all given needles are present in the array as key/index.
     *
     * @param array $needles   <p>The keys you are searching for.</p>
     * @param bool  $recursive
     *
     * @return bool
     *              <p>Returns true if all the given keys/indexes exists in the array, false otherwise.</p>
     */
    public function containsKeys(array $needles, $recursive = false): bool
    {
        if ($recursive === true) {
            return \count(
                       \array_intersect($needles, $this->keys(true)->getArray()),
                       \COUNT_RECURSIVE
                   )
                   ===
                   \count(
                       $needles,
                       \COUNT_RECURSIVE
                   );
        }

        return \count(
                   \array_intersect($needles, $this->keys()->getArray()),
                   \COUNT_NORMAL
               )
               ===
               \count(
                   $needles,
                   \COUNT_NORMAL
               );
    }

    /**
     * Check if all given needles are present in the array as key/index.
     *
     * @param array $needles <p>The keys you are searching for.</p>
     *
     * @return bool
     *              <p>Returns true if all the given keys/indexes exists in the array, false otherwise.</p>
     */
    public function containsKeysRecursive(array $needles): bool
    {
        return $this->containsKeys($needles, true);
    }

    /**
     * alias: for "Arrayy->contains()"
     *
     * @param float|int|string $value
     *
     * @return bool
     *
     * @see Arrayy::contains()
     */
    public function containsValue($value): bool
    {
        return $this->contains($value);
    }

    /**
     * alias: for "Arrayy->contains($value, true)"
     *
     * @param float|int|string $value
     *
     * @return bool
     *
     * @see Arrayy::contains()
     */
    public function containsValueRecursive($value): bool
    {
        return $this->contains($value, true);
    }

    /**
     * Check if all given needles are present in the array.
     *
     * @param array $needles
     *
     * @return bool
     *              <p>Returns true if all the given values exists in the array, false otherwise.</p>
     */
    public function containsValues(array $needles): bool
    {
        return \count(\array_intersect($needles, $this->getArray()), \COUNT_NORMAL)
               ===
               \count($needles, \COUNT_NORMAL);
    }

    /**
     * Counts all the values of an array
     *
     * @see http://php.net/manual/en/function.array-count-values.php
     *
     * @return static
     *                <p>
     *                (Immutable)
     *                An associative Arrayy-object of values from input as
     *                keys and their count as value.
     *                </p>
     */
    public function countValues(): self
    {
        return new static(\array_count_values($this->getArray()));
    }

    /**
     * Creates an Arrayy object.
     *
     * @param mixed  $array
     * @param string $iteratorClass
     * @param bool   $checkForMissingPropertiesInConstructor
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     */
    public static function create($array = [], string $iteratorClass = ArrayyIterator::class, bool $checkForMissingPropertiesInConstructor = true): self
    {
        return new static(
            $array,
            $iteratorClass,
            $checkForMissingPropertiesInConstructor
        );
    }

    /**
     * WARNING: Creates an Arrayy object by reference.
     *
     * @param array $array
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function createByReference(array &$array = []): self
    {
        $array = $this->fallbackForArray($array);

        $this->array = &$array;

        return $this;
    }

    /**
     * Create an new instance from a callable function which will return an Generator.
     *
     * @param callable():Generator $generatorFunction
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     */
    public static function createFromGeneratorFunction(callable $generatorFunction): self
    {
        $arrayy = new static($generatorFunction);

        return $arrayy;
    }

    /**
     * Create an new instance filled with a copy of values from a "Generator"-object.
     *
     * @param \Generator $generator
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     */
    public static function createFromGeneratorImmutable(\Generator $generator): self
    {
        // init
        $arrayy = new static();

        foreach ($generator as $key => $value) {
            $arrayy[$key] = $value;
        }

        return $arrayy;
    }

    /**
     * Create an new Arrayy object via JSON.
     *
     * @param string $json
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     */
    public static function createFromJson(string $json): self
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        return static::create(\json_decode($json, true));
    }

    /**
     * Create an new instance filled with values from an object that have implemented ArrayAccess.
     *
     * @param \ArrayAccess $object <p>Object that implements ArrayAccess</p>
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     */
    public static function createFromObject(\ArrayAccess $object): self
    {
        // init
        $array = new static();

        if ($object instanceof self) {
            $objectArray = $object->getGenerator();
        } else {
            $objectArray = $object;
        }

        foreach ($objectArray as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Create an new instance filled with values from an object.
     *
     * @param object $object
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     */
    public static function createFromObjectVars($object): self
    {
        return new static(self::objectToArray($object));
    }

    /**
     * Create an new Arrayy object via string.
     *
     * @param string      $str       <p>The input string.</p>
     * @param string|null $delimiter <p>The boundary string.</p>
     * @param string|null $regEx     <p>Use the $delimiter or the $regEx, so if $pattern is null, $delimiter will be
     *                               used.</p>
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     */
    public static function createFromString(string $str, string $delimiter = null, string $regEx = null): self
    {
        if ($regEx) {
            \preg_match_all($regEx, $str, $array);

            if (!empty($array)) {
                $array = $array[0];
            }
        } else {
            $array = \explode($delimiter, $str);
        }

        // trim all string in the array
        \array_walk(
            $array,
            static function (&$val) {
                if (\is_string($val)) {
                    $val = \trim($val);
                }
            }
        );

        return static::create($array);
    }

    /**
     * Create an new instance containing a range of elements.
     *
     * @param mixed $low  <p>First value of the sequence.</p>
     * @param mixed $high <p>The sequence is ended upon reaching the end value.</p>
     * @param int   $step <p>Used as the increment between elements in the sequence.</p>
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     */
    public static function createWithRange($low, $high, int $step = 1): self
    {
        return static::create(\range($low, $high, $step));
    }

    /**
     * Custom sort by index via "uksort".
     *
     * @see http://php.net/manual/en/function.uksort.php
     *
     * @param \callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function customSortKeys($function): self
    {
        if (!\is_callable($function)) {
            throw new \InvalidArgumentException(
                'Passed function must be callable'
            );
        }

        $this->generatorToArray();

        \uksort($this->array, $function);

        return $this;
    }

    /**
     * Custom sort by value via "usort".
     *
     * @see http://php.net/manual/en/function.usort.php
     *
     * @param \callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function customSortValues($function): self
    {
        if (!\is_callable($function)) {
            throw new \InvalidArgumentException(
                'Passed function must be callable'
            );
        }

        $this->generatorToArray();

        \usort($this->array, $function);

        return $this;
    }

    /**
     * Return values that are only in the current array.
     *
     * @param array $array
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function diff(array $array = []): self
    {
        return static::create(
            \array_diff($this->getArray(), $array),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return values that are only in the current multi-dimensional array.
     *
     * @param array      $array
     * @param array|null $helperVariableForRecursion <p>(only for internal usage)</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function diffRecursive(array $array = [], $helperVariableForRecursion = null): self
    {
        // init
        $result = [];

        if (
            $helperVariableForRecursion !== null
            &&
            \is_array($helperVariableForRecursion)
        ) {
            $arrayForTheLoop = $helperVariableForRecursion;
        } else {
            $arrayForTheLoop = $this->getGenerator();
        }

        foreach ($arrayForTheLoop as $key => $value) {
            if ($value instanceof self) {
                $value = $value->getArray();
            }

            if (\array_key_exists($key, $array)) {
                if ($value !== $array[$key]) {
                    $result[$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return values that are only in the new $array.
     *
     * @param array $array
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function diffReverse(array $array = []): self
    {
        return static::create(
            \array_diff($array, $this->getArray()),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function divide(): self
    {
        return static::create(
            [
                $this->keys(),
                $this->values(),
            ],
            $this->iteratorClass,
            false
        );
    }

    /**
     * Iterate over the current array and modify the array's value.
     *
     * @param \Closure $closure
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function each(\Closure $closure): self
    {
        // init
        $array = [];

        foreach ($this->getGenerator() as $key => $value) {
            $array[$key] = $closure($value, $key);
        }

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Check if a value is in the current array using a closure.
     *
     * @param \Closure $closure
     *
     * @return bool
     *              <p>Returns true if the given value is found, false otherwise.</p>
     */
    public function exists(\Closure $closure): bool
    {
        // init
        $isExists = false;

        foreach ($this->getGenerator() as $key => $value) {
            if ($closure($value, $key)) {
                $isExists = true;

                break;
            }
        }

        return $isExists;
    }

    /**
     * Fill the array until "$num" with "$default" values.
     *
     * @param int   $num
     * @param mixed $default
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function fillWithDefaults(int $num, $default = null): self
    {
        if ($num < 0) {
            throw new \InvalidArgumentException('The $num parameter can only contain non-negative values.');
        }

        $this->generatorToArray();

        $tmpArray = $this->array;

        $count = \count($tmpArray);

        while ($count < $num) {
            $tmpArray[] = $default;
            ++$count;
        }

        return static::create(
            $tmpArray,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Find all items in an array that pass the truth test.
     *
     * @param \Closure|null $closure [optional] <p>
     *                               The callback function to use
     *                               </p>
     *                               <p>
     *                               If no callback is supplied, all entries of
     *                               input equal to false (see
     *                               converting to
     *                               boolean) will be removed.
     *                               </p>
     *                               * @param int $flag [optional] <p>
     *                               Flag determining what arguments are sent to <i>callback</i>:
     *                               </p><ul>
     *                               <li>
     *                               <b>ARRAY_FILTER_USE_KEY</b> [1] - pass key as the only argument
     *                               to <i>callback</i> instead of the value</span>
     *                               </li>
     *                               <li>
     *                               <b>ARRAY_FILTER_USE_BOTH</b> [2] - pass both value and key as
     *                               arguments to <i>callback</i> instead of the value</span>
     *                               </li>
     *                               </ul>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function filter($closure = null, int $flag = \ARRAY_FILTER_USE_BOTH): self
    {
        if (!$closure) {
            return $this->clean();
        }

        return static::create(
            \array_filter($this->getArray(), $closure, $flag),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Filters an array of objects (or a numeric array of associative arrays) based on the value of a particular
     * property within that.
     *
     * @param string          $property
     * @param string|string[] $value
     * @param string          $comparisonOp
     *                                      <p>
     *                                      'eq' (equals),<br />
     *                                      'gt' (greater),<br />
     *                                      'gte' || 'ge' (greater or equals),<br />
     *                                      'lt' (less),<br />
     *                                      'lte' || 'le' (less or equals),<br />
     *                                      'ne' (not equals),<br />
     *                                      'contains',<br />
     *                                      'notContains',<br />
     *                                      'newer' (via strtotime),<br />
     *                                      'older' (via strtotime),<br />
     *                                      </p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function filterBy(string $property, $value, string $comparisonOp = null): self
    {
        if (!$comparisonOp) {
            $comparisonOp = \is_array($value) ? 'contains' : 'eq';
        }

        $ops = [
            'eq' => static function ($item, $prop, $value) {
                return $item[$prop] === $value;
            },
            'gt' => static function ($item, $prop, $value) {
                return $item[$prop] > $value;
            },
            'ge' => static function ($item, $prop, $value) {
                return $item[$prop] >= $value;
            },
            'gte' => static function ($item, $prop, $value) {
                return $item[$prop] >= $value;
            },
            'lt' => static function ($item, $prop, $value) {
                return $item[$prop] < $value;
            },
            'le' => static function ($item, $prop, $value) {
                return $item[$prop] <= $value;
            },
            'lte' => static function ($item, $prop, $value) {
                return $item[$prop] <= $value;
            },
            'ne' => static function ($item, $prop, $value) {
                return $item[$prop] !== $value;
            },
            'contains' => static function ($item, $prop, $value) {
                return \in_array($item[$prop], (array) $value, true);
            },
            'notContains' => static function ($item, $prop, $value) {
                return !\in_array($item[$prop], (array) $value, true);
            },
            'newer' => static function ($item, $prop, $value) {
                return \strtotime($item[$prop]) > \strtotime($value);
            },
            'older' => static function ($item, $prop, $value) {
                return \strtotime($item[$prop]) < \strtotime($value);
            },
        ];

        $result = \array_values(
            \array_filter(
                $this->getArray(),
                static function ($item) use (
                    $property,
                    $value,
                    $ops,
                    $comparisonOp
                ) {
                    $item = (array) $item;
                    $itemArrayy = new static($item);
                    $item[$property] = $itemArrayy->get($property, []);

                    return $ops[$comparisonOp]($item, $property, $value);
                }
            )
        );

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Find the first item in an array that passes the truth test,
     *  otherwise return false
     *
     * @param \Closure $closure
     *
     * @return false|mixed
     *                     <p>Return false if we did not find the value.</p>
     */
    public function find(\Closure $closure)
    {
        foreach ($this->getGenerator() as $key => $value) {
            if ($closure($value, $key)) {
                return $value;
            }
        }

        return false;
    }

    /**
     * find by ...
     *
     * @param string          $property
     * @param string|string[] $value
     * @param string          $comparisonOp
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function findBy(string $property, $value, string $comparisonOp = 'eq'): self
    {
        return $this->filterBy($property, $value, $comparisonOp);
    }

    /**
     * Get the first value from the current array.
     *
     * @return mixed
     *               <p>Return null if there wasn't a element.</p>
     */
    public function first()
    {
        $tmpArray = $this->getArray();

        return \array_shift($tmpArray);
    }

    /**
     * Get the first value(s) from the current array.
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function firstsImmutable(int $number = null): self
    {
        $arrayTmp = $this->getArray();

        if ($number === null) {
            $array = (array) \array_shift($arrayTmp);
        } else {
            $number = (int) $number;
            $array = \array_splice($arrayTmp, 0, $number);
        }

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get the first value(s) from the current array.
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static
     *                <p>(Mutable)</p>
     */
    public function firstsMutable(int $number = null): self
    {
        $this->generatorToArray();

        if ($number === null) {
            $this->array = (array) \array_shift($this->array);
        } else {
            $number = (int) $number;
            $this->array = \array_splice($this->array, 0, $number);
        }

        return $this;
    }

    /**
     * Exchanges all keys with their associated values in an array.
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function flip(): self
    {
        return static::create(
            \array_flip($this->getArray()),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get a value from an array (optional using dot-notation).
     *
     * @param mixed $key      <p>The key to look for.</p>
     * @param mixed $fallback <p>Value to fallback to.</p>
     * @param array $array    <p>The array to get from, if it's set to "null" we use the current array from the
     *                        class.</p>
     *
     * @return mixed|static
     */
    public function get($key, $fallback = null, array $array = null)
    {
        if ($array !== null) {
            $usedArray = $array;
        } else {
            $this->generatorToArray();

            $usedArray = $this->array;
        }

        if ($key === null) {
            return static::create(
                $usedArray,
                $this->iteratorClass,
                false
            );
        }

        // php cast "bool"-index into "int"-index
        if ((bool) $key === $key) {
            $key = (int) $key;
        }

        if (\array_key_exists($key, $usedArray) === true) {
            if (\is_array($usedArray[$key])) {
                return static::create(
                    $usedArray[$key],
                    $this->iteratorClass,
                    false
                );
            }

            return $usedArray[$key];
        }

        // crawl through array, get key according to object or not
        foreach (\explode($this->pathSeparator, (string) $key) as $segment) {
            if (!isset($usedArray[$segment])) {
                return $fallback instanceof \Closure ? $fallback() : $fallback;
            }

            $usedArray = $usedArray[$segment];
        }

        if (\is_array($usedArray)) {
            return static::create(
                $usedArray,
                $this->iteratorClass,
                false
            );
        }

        return $usedArray;
    }

    /**
     * Get the current array from the "Arrayy"-object.
     *
     * @return array
     */
    public function getArray(): array
    {
        // init
        $array = [];

        foreach ($this->getGenerator() as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->getArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey, can be used to extract data-columns from multi-arrays.
     *
     * Info: Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param mixed $columnKey
     * @param mixed $indexKey
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function getColumn($columnKey = null, $indexKey = null): self
    {
        return static::create(
            \array_column($this->getArray(), $columnKey, $indexKey),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get the current array from the "Arrayy"-object as generator.
     *
     * @return \Generator
     */
    public function getGenerator(): \Generator
    {
        if ($this->generator instanceof ArrayyRewindableGenerator) {
            yield from $this->generator;
        }

        yield from $this->array;
    }

    /**
     * alias: for "Arrayy->keys()"
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @see Arrayy::keys()
     */
    public function getKeys(): self
    {
        return $this->keys();
    }

    /**
     * Get the current array from the "Arrayy"-object as object.
     *
     * @return \stdClass
     */
    public function getObject(): \stdClass
    {
        return self::arrayToObject($this->getArray());
    }

    /**
     * alias: for "Arrayy->randomImmutable()"
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @see Arrayy::randomImmutable()
     */
    public function getRandom(): self
    {
        return $this->randomImmutable();
    }

    /**
     * alias: for "Arrayy->randomKey()"
     *
     * @return mixed
     *               <p>Get a key/index or null if there wasn't a key/index.</p>
     *
     * @see Arrayy::randomKey()
     */
    public function getRandomKey()
    {
        return $this->randomKey();
    }

    /**
     * alias: for "Arrayy->randomKeys()"
     *
     * @param int $number
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @see Arrayy::randomKeys()
     */
    public function getRandomKeys(int $number): self
    {
        return $this->randomKeys($number);
    }

    /**
     * alias: for "Arrayy->randomValue()"
     *
     * @return mixed
     *               <p>Get a random value or null if there wasn't a value.</p>
     *
     * @see Arrayy::randomValue()
     */
    public function getRandomValue()
    {
        return $this->randomValue();
    }

    /**
     * alias: for "Arrayy->randomValues()"
     *
     * @param int $number
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @see Arrayy::randomValues()
     */
    public function getRandomValues(int $number): self
    {
        return $this->randomValues($number);
    }

    /**
     * Group values from a array according to the results of a closure.
     *
     * @param \callable $grouper  <p>A callable function name.</p>
     * @param bool      $saveKeys
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function group($grouper, bool $saveKeys = false): self
    {
        // init
        $result = [];

        // Iterate over values, group by property/results from closure.
        foreach ($this->getGenerator() as $key => $value) {
            $groupKey = \is_callable($grouper) ? $grouper($value, $key) : $this->get($grouper, null, $this->getArray());
            $newValue = $this->get($groupKey, null, $result);

            if ($groupKey instanceof self) {
                $groupKey = $groupKey->getArray();
            }

            if ($newValue instanceof self) {
                $newValue = $newValue->getArray();
            }

            // Add to results.
            if ($groupKey !== null) {
                if ($saveKeys) {
                    $result[$groupKey] = $newValue;
                    $result[$groupKey][$key] = $value;
                } else {
                    $result[$groupKey] = $newValue;
                    $result[$groupKey][] = $value;
                }
            }
        }

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Check if an array has a given key.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        static $UN_FOUND = null;

        if ($UN_FOUND === null) {
            // Generate unique string to use as marker.
            $UN_FOUND = \uniqid('arrayy', true);
        }

        return $this->get($key, $UN_FOUND) !== $UN_FOUND;
    }

    /**
     * Implodes the values of this array.
     *
     * @param string $glue
     *
     * @return string
     */
    public function implode(string $glue = ''): string
    {
        return $this->implode_recursive($glue, $this->getArray(), false);
    }

    /**
     * Implodes the keys of this array.
     *
     * @param string $glue
     *
     * @return string
     */
    public function implodeKeys(string $glue = ''): string
    {
        return $this->implode_recursive($glue, $this->getArray(), true);
    }

    /**
     * Given a list and an iterate-function that returns
     * a key for each element in the list (or a property name),
     * returns an object with an index of each item.
     *
     * @param mixed $key
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function indexBy($key): self
    {
        // init
        $results = [];

        foreach ($this->getGenerator() as $a) {
            if (\array_key_exists($key, $a) === true) {
                $results[$a[$key]] = $a;
            }
        }

        return static::create(
            $results,
            $this->iteratorClass,
            false
        );
    }

    /**
     * alias: for "Arrayy->searchIndex()"
     *
     * @param mixed $value <p>The value to search for.</p>
     *
     * @return mixed
     *
     * @see Arrayy::searchIndex()
     */
    public function indexOf($value)
    {
        return $this->searchIndex($value);
    }

    /**
     * Get everything but the last..$to items.
     *
     * @param int $to
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function initial(int $to = 1): self
    {
        return $this->firstsImmutable(\count($this->getArray(), \COUNT_NORMAL) - $to);
    }

    /**
     * Return an array with all elements found in input array.
     *
     * @param array $search
     * @param bool  $keepKeys
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function intersection(array $search, bool $keepKeys = false): self
    {
        if ($keepKeys) {
            return static::create(
                \array_uintersect(
                    $this->array,
                    $search,
                    static function ($a, $b) {
                        return $a === $b ? 0 : -1;
                    }
                ),
                $this->iteratorClass,
                false
            );
        }

        return static::create(
            \array_values(\array_intersect($this->getArray(), $search)),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return a boolean flag which indicates whether the two input arrays have any common elements.
     *
     * @param array $search
     *
     * @return bool
     */
    public function intersects(array $search): bool
    {
        return \count($this->intersection($search)->array, \COUNT_NORMAL) > 0;
    }

    /**
     * Invoke a function on all of an array's values.
     *
     * @param mixed $callable
     * @param mixed $arguments
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function invoke($callable, $arguments = []): self
    {
        // If one argument given for each iteration, create an array for it.
        if (!\is_array($arguments)) {
            $arguments = StaticArrayy::repeat(
                $arguments,
                \count($this->getArray(), \COUNT_NORMAL)
            )->getArray();
        }

        // If the callable has arguments, pass them.
        if ($arguments) {
            $array = \array_map($callable, $this->getArray(), $arguments);
        } else {
            $array = \array_map($callable, $this->getArray());
        }

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Check whether array is associative or not.
     *
     * @param bool $recursive
     *
     * @return bool
     *              <p>Returns true if associative, false otherwise.</p>
     */
    public function isAssoc(bool $recursive = false): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        foreach ($this->keys($recursive)->getGenerator() as $key) {
            if (!\is_string($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check whether the array is empty or not.
     *
     * @return bool
     *              <p>Returns true if empty, false otherwise.</p>
     */
    public function isEmpty(): bool
    {
        if ($this->generator) {
            return $this->getArray() === [];
        }

        return $this->array === [];
    }

    /**
     * Check if the current array is equal to the given "$array" or not.
     *
     * @param array $array
     *
     * @return bool
     */
    public function isEqual(array $array): bool
    {
        return $this->getArray() === $array;
    }

    /**
     * Check if the current array is a multi-array.
     *
     * @return bool
     */
    public function isMultiArray(): bool
    {
        return !(
            \count($this->getArray(), \COUNT_NORMAL)
            ===
            \count($this->getArray(), \COUNT_RECURSIVE)
        );
    }

    /**
     * Check whether array is numeric or not.
     *
     * @return bool
     *              <p>Returns true if numeric, false otherwise.</p>
     */
    public function isNumeric(): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        foreach ($this->keys()->getGenerator() as $key) {
            if (!\is_int($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the current array is sequential [0, 1, 2, 3, 4, 5 ...] or not.
     *
     * @param bool $recursive
     *
     * @return bool
     */
    public function isSequential(bool $recursive = false): bool
    {

        // recursive

        if ($recursive === true) {
            return $this->array_keys_recursive($this->getArray())
                   ===
                   \range(0, \count($this->getArray(), \COUNT_RECURSIVE) - 1);
        }

        // non recursive

        return \array_keys($this->getArray())
               ===
               \range(0, \count($this->getArray(), \COUNT_NORMAL) - 1);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->getArray();
    }

    /**
     * Get all keys from the current array.
     *
     * @param bool  $recursive    [optional] <p>
     *                            Get all keys, also from all sub-arrays from an multi-dimensional array.
     *                            </p>
     * @param mixed $search_value [optional] <p>
     *                            If specified, then only keys containing these values are returned.
     *                            </p>
     * @param bool  $strict       [optional] <p>
     *                            Determines if strict comparison (===) should be used during the search.
     *                            </p>
     *
     * @return static
     *                <p>(Immutable) An array of all the keys in input.</p>
     */
    public function keys(bool $recursive = false, $search_value = null, bool $strict = true): self
    {

        // recursive

        if ($recursive === true) {
            if ($search_value === null) {
                $array = $this->array_keys_recursive($this->getArray());
            } else {
                $array = $this->array_keys_recursive($this->getArray(), $search_value, $strict);
            }

            return static::create(
                $array,
                $this->iteratorClass,
                false
            );
        }

        // non recursive

        if ($search_value === null) {
            $array = \array_keys($this->getArray());
        } else {
            $array = \array_keys($this->getArray(), $search_value, $strict);
        }

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Sort an array by key in reverse order.
     *
     * @param int $sort_flags [optional] <p>
     *                        You may modify the behavior of the sort using the optional
     *                        parameter sort_flags, for details
     *                        see sort.
     *                        </p>
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function krsort(int $sort_flags = 0): self
    {
        $this->generatorToArray();

        \krsort($this->array, $sort_flags);

        return $this;
    }

    /**
     * Get the last value from the current array.
     *
     * @return mixed
     *               <p>Return null if there wasn't a element.</p>
     */
    public function last()
    {
        return $this->pop();
    }

    /**
     * Get the last value(s) from the current array.
     *
     * @param int|null $number
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function lastsImmutable(int $number = null): self
    {
        if ($this->isEmpty()) {
            return static::create(
                [],
                $this->iteratorClass,
                false
            );
        }

        if ($number === null) {
            $poppedValue = $this->pop();

            if ($poppedValue === null) {
                $poppedValue = [$poppedValue];
            } else {
                $poppedValue = (array) $poppedValue;
            }

            $arrayy = static::create(
                $poppedValue,
                $this->iteratorClass,
                false
            );
        } else {
            $number = (int) $number;
            $arrayy = $this->rest(-$number);
        }

        return $arrayy;
    }

    /**
     * Get the last value(s) from the current array.
     *
     * @param int|null $number
     *
     * @return static
     *                <p>(Mutable)</p>
     */
    public function lastsMutable(int $number = null): self
    {
        if ($this->isEmpty()) {
            return $this;
        }

        if ($number === null) {
            $poppedValue = $this->pop();

            if ($poppedValue === null) {
                $poppedValue = [$poppedValue];
            } else {
                $poppedValue = (array) $poppedValue;
            }

            $this->array = static::create(
                $poppedValue,
                $this->iteratorClass,
                false
            )->getArray();
        } else {
            $number = (int) $number;
            $this->array = $this->rest(-$number)->getArray();
        }

        $this->generator = null;

        return $this;
    }

    /**
     * Count the values from the current array.
     *
     * alias: for "Arrayy->count()"
     *
     * @param int $mode
     *
     * @return int
     *
     * @see Arrayy::count()
     */
    public function length(int $mode = \COUNT_NORMAL): int
    {
        return $this->count($mode);
    }

    /**
     * Apply the given function to the every element of the array,
     * collecting the results.
     *
     * @param \callable $callable
     *
     * @return static
     *                <p>(Immutable) Arrayy object with modified elements.</p>
     */
    public function map(callable $callable): self
    {
        return static::create(
            \array_map($callable, $this->getArray()),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Check if all items in current array match a truth test.
     *
     * @param \Closure $closure
     *
     * @return bool
     */
    public function matches(\Closure $closure): bool
    {
        if (\count($this->getArray(), \COUNT_NORMAL) === 0) {
            return false;
        }

        foreach ($this->getGenerator() as $key => $value) {
            $value = $closure($value, $key);

            if ($value === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if any item in the current array matches a truth test.
     *
     * @param \Closure $closure
     *
     * @return bool
     */
    public function matchesAny(\Closure $closure): bool
    {
        if (\count($this->getArray(), \COUNT_NORMAL) === 0) {
            return false;
        }

        foreach ($this->getGenerator() as $key => $value) {
            $value = $closure($value, $key);

            if ($value === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the max value from an array.
     *
     * @return mixed
     */
    public function max()
    {
        if (\count($this->getArray(), \COUNT_NORMAL) === 0) {
            return false;
        }

        return \max($this->getArray());
    }

    /**
     * Merge the new $array into the current array.
     *
     * - keep key,value from the current array, also if the index is in the new $array
     *
     * @param array $array
     * @param bool  $recursive
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function mergeAppendKeepIndex(array $array = [], bool $recursive = false): self
    {
        if ($recursive === true) {
            $result = \array_replace_recursive($this->getArray(), $array);
        } else {
            $result = \array_replace($this->getArray(), $array);
        }

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Merge the new $array into the current array.
     *
     * - replace duplicate assoc-keys from the current array with the key,values from the new $array
     * - create new indexes
     *
     * @param array $array
     * @param bool  $recursive
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function mergeAppendNewIndex(array $array = [], bool $recursive = false): self
    {
        if ($recursive === true) {
            $result = \array_merge_recursive($this->getArray(), $array);
        } else {
            $result = \array_merge($this->getArray(), $array);
        }

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Merge the the current array into the $array.
     *
     * - use key,value from the new $array, also if the index is in the current array
     *
     * @param array $array
     * @param bool  $recursive
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function mergePrependKeepIndex(array $array = [], bool $recursive = false): self
    {
        if ($recursive === true) {
            $result = \array_replace_recursive($array, $this->getArray());
        } else {
            $result = \array_replace($array, $this->getArray());
        }

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Merge the current array into the new $array.
     *
     * - replace duplicate assoc-keys from new $array with the key,values from the current array
     * - create new indexes
     *
     * @param array $array
     * @param bool  $recursive
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function mergePrependNewIndex(array $array = [], bool $recursive = false): self
    {
        if ($recursive === true) {
            $result = \array_merge_recursive($array, $this->getArray());
        } else {
            $result = \array_merge($array, $this->getArray());
        }

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * @return ArrayyMeta|static
     */
    public static function meta()
    {
        return (new ArrayyMeta())->getMetaObject(static::class);
    }

    /**
     * Get the min value from an array.
     *
     * @return mixed
     */
    public function min()
    {
        if (\count($this->getArray(), \COUNT_NORMAL) === 0) {
            return false;
        }

        return \min($this->getArray());
    }

    /**
     * Move an array element to a new index.
     *
     * cherry-picked from: http://stackoverflow.com/questions/12624153/move-an-array-element-to-a-new-index-in-php
     *
     * @param int|string $from
     * @param int|string $to
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function moveElement($from, $to): self
    {
        $array = $this->getArray();

        if (\is_int($from)) {
            $tmp = \array_splice($array, $from, 1);
            \array_splice($array, $to, 0, $tmp);
            $output = $array;
        } elseif (\is_string($from)) {
            $indexToMove = \array_search($from, \array_keys($array), true);
            $itemToMove = $array[$from];
            \array_splice($array, $indexToMove, 1);
            $i = 0;
            $output = [];
            foreach ($array as $key => $item) {
                if ($i === $to) {
                    $output[$from] = $itemToMove;
                }
                $output[$key] = $item;
                ++$i;
            }
        } else {
            $output = [];
        }

        return static::create(
            $output,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param mixed[] $keys
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function only(array $keys): self
    {
        $array = $this->getArray();

        return static::create(
            \array_intersect_key($array, \array_flip($keys)),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Pad array to the specified size with a given value.
     *
     * @param int   $size  <p>Size of the result array.</p>
     * @param mixed $value <p>Empty value by default.</p>
     *
     * @return static
     *                <p>(Immutable) Arrayy object padded to $size with $value.</p>
     */
    public function pad(int $size, $value): self
    {
        return static::create(
            \array_pad($this->getArray(), $size, $value),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Pop a specified value off the end of the current array.
     *
     * @return mixed
     *               <p>(Mutable) The popped element from the current array.</p>
     */
    public function pop()
    {
        $this->generatorToArray();

        return \array_pop($this->array);
    }

    /**
     * Prepend a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with the prepended value.</p>
     */
    public function prepend($value, $key = null): self
    {
        $this->generatorToArray();

        if ($key === null) {
            \array_unshift($this->array, $value);
        } else {
            /** @noinspection AdditionOperationOnArraysInspection */
            $this->array = [$key => $value] + $this->array;
        }

        return $this;
    }

    /**
     * Add a suffix to each key.
     *
     * @param mixed $suffix
     *
     * @return static
     *                <p>(Immutable) Return an Arrayy object, with the prepended keys.</p>
     */
    public function prependToEachKey($suffix): self
    {
        // init
        $result = [];

        foreach ($this->getGenerator() as $key => $item) {
            if ($item instanceof self) {
                $result[$key] = $item->prependToEachKey($suffix);
            } elseif (\is_array($item)) {
                $result[$key] = self::create(
                    $item,
                    $this->iteratorClass,
                    false
                )->prependToEachKey($suffix)
                    ->toArray();
            } else {
                $result[$key . $suffix] = $item;
            }
        }

        return self::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Add a suffix to each value.
     *
     * @param mixed $suffix
     *
     * @return static
     *                <p>(Immutable) Return an Arrayy object, with the prepended values.</p>
     */
    public function prependToEachValue($suffix): self
    {
        // init
        $result = [];

        foreach ($this->getGenerator() as $key => $item) {
            if ($item instanceof self) {
                $result[$key] = $item->prependToEachValue($suffix);
            } elseif (\is_array($item)) {
                $result[$key] = self::create(
                    $item,
                    $this->iteratorClass,
                    false
                )->prependToEachValue($suffix)
                    ->toArray();
            } elseif (\is_object($item)) {
                $result[$key] = $item;
            } else {
                $result[$key] = $item . $suffix;
            }
        }

        return self::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Push one or more values onto the end of array at once.
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with pushed elements to the end of array.</p>
     */
    public function push(/* variadic arguments allowed */): self
    {
        $this->generatorToArray();

        if (\func_num_args()) {
            $args = \array_merge([&$this->array], \func_get_args());
            \array_push(...$args);
        }

        return $this;
    }

    /**
     * Get a random value from the current array.
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function randomImmutable(int $number = null): self
    {
        $this->generatorToArray();

        if (\count($this->array, \COUNT_NORMAL) === 0) {
            return static::create(
                [],
                $this->iteratorClass,
                false
            );
        }

        if ($number === null) {
            /** @noinspection NonSecureArrayRandUsageInspection */
            $arrayRandValue = [$this->array[\array_rand($this->array)]];

            return static::create(
                $arrayRandValue,
                $this->iteratorClass,
                false
            );
        }

        $arrayTmp = $this->array;
        /** @noinspection NonSecureShuffleUsageInspection */
        \shuffle($arrayTmp);

        return static::create(
            $arrayTmp,
            $this->iteratorClass,
            false
        )->firstsImmutable($number);
    }

    /**
     * Pick a random key/index from the keys of this array.
     *
     * @throws \RangeException If array is empty
     *
     * @return mixed
     *               <p>Get a key/index or null if there wasn't a key/index.</p>
     */
    public function randomKey()
    {
        $result = $this->randomKeys(1);

        if (!isset($result[0])) {
            $result[0] = null;
        }

        return $result[0];
    }

    /**
     * Pick a given number of random keys/indexes out of this array.
     *
     * @param int $number <p>The number of keys/indexes (should be <= \count($this->array))</p>
     *
     * @throws \RangeException If array is empty
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function randomKeys(int $number): self
    {
        $this->generatorToArray();

        $count = \count($this->array, \COUNT_NORMAL);

        if ($number === 0 || $number > $count) {
            throw new \RangeException(
                \sprintf(
                    'Number of requested keys (%s) must be equal or lower than number of elements in this array (%s)',
                    $number,
                    $count
                )
            );
        }

        $result = (array) \array_rand($this->array, $number);

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get a random value from the current array.
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static
     *                <p>(Mutable)</p>
     */
    public function randomMutable(int $number = null): self
    {
        $this->generatorToArray();

        if (\count($this->array, \COUNT_NORMAL) === 0) {
            return static::create(
                [],
                $this->iteratorClass,
                false
            );
        }

        if ($number === null) {
            /** @noinspection NonSecureArrayRandUsageInspection */
            $arrayRandValue = [$this->array[\array_rand($this->array)]];
            $this->array = $arrayRandValue;

            return $this;
        }

        /** @noinspection NonSecureShuffleUsageInspection */
        \shuffle($this->array);

        return $this->firstsMutable($number);
    }

    /**
     * Pick a random value from the values of this array.
     *
     * @return mixed
     *               <p>Get a random value or null if there wasn't a value.</p>
     */
    public function randomValue()
    {
        $result = $this->randomImmutable();

        if (!isset($result[0])) {
            $result[0] = null;
        }

        return $result[0];
    }

    /**
     * Pick a given number of random values out of this array.
     *
     * @param int $number
     *
     * @return static
     *                <p>(Mutable)</p>
     */
    public function randomValues(int $number): self
    {
        return $this->randomMutable($number);
    }

    /**
     * Get a random value from an array, with the ability to skew the results.
     *
     * Example: randomWeighted(['foo' => 1, 'bar' => 2]) has a 66% chance of returning bar.
     *
     * @param array    $array
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function randomWeighted(array $array, int $number = null): self
    {
        // init
        $options = [];

        foreach ($array as $option => $weight) {
            if ($this->searchIndex($option) !== false) {
                for ($i = 0; $i < $weight; ++$i) {
                    $options[] = $option;
                }
            }
        }

        return $this->mergeAppendKeepIndex($options)->randomImmutable($number);
    }

    /**
     * Reduce the current array via callable e.g. anonymous-function.
     *
     * @param \callable $callable
     * @param array     $init
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function reduce($callable, array $init = []): self
    {
        if ($this->generator) {
            $result = $init;

            foreach ($this->getGenerator() as $value) {
                $result = $callable($result, $value);
            }

            return static::create(
                $result,
                $this->iteratorClass,
                false
            );
        }

        $result = \array_reduce($this->array, $callable, $init);

        if ($result === null) {
            $this->array = [];
        } else {
            $this->array = (array) $result;
        }

        return static::create(
            $this->array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Create a numerically re-indexed Arrayy object.
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with re-indexed array-elements.</p>
     */
    public function reindex(): self
    {
        $this->generatorToArray();

        $this->array = \array_values($this->array);

        return $this;
    }

    /**
     * Return all items that fail the truth test.
     *
     * @param \Closure $closure
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function reject(\Closure $closure): self
    {
        // init
        $filtered = [];

        foreach ($this->getGenerator() as $key => $value) {
            if (!$closure($value, $key)) {
                $filtered[$key] = $value;
            }
        }

        return static::create(
            $filtered,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Remove a value from the current array (optional using dot-notation).
     *
     * @param mixed $key
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function remove($key): self
    {
        // recursive call
        if (\is_array($key)) {
            foreach ($key as $k) {
                $this->internalRemove($k);
            }

            return static::create(
                $this->getArray(),
                $this->iteratorClass,
                false
            );
        }

        $this->internalRemove($key);

        return static::create(
            $this->getArray(),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Remove the first value from the current array.
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function removeFirst(): self
    {
        $tmpArray = $this->getArray();

        \array_shift($tmpArray);

        return static::create(
            $tmpArray,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Remove the last value from the current array.
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function removeLast(): self
    {
        $tmpArray = $this->getArray();

        \array_pop($tmpArray);

        return static::create(
            $tmpArray,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Removes a particular value from an array (numeric or associative).
     *
     * @param mixed $value
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function removeValue($value): self
    {
        $this->generatorToArray();

        // init
        $isNumericArray = true;

        foreach ($this->getGenerator() as $key => $item) {
            if ($item === $value) {
                if (!\is_int($key)) {
                    $isNumericArray = false;
                }
                unset($this->array[$key]);
            }
        }

        if ($isNumericArray) {
            $this->array = \array_values($this->array);
        }

        return static::create(
            $this->array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Generate array of repeated arrays.
     *
     * @param int $times <p>How many times has to be repeated.</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function repeat($times): self
    {
        if ($times === 0) {
            return new static();
        }

        return static::create(
            \array_fill(0, (int) $times, $this->getArray()),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Replace a key with a new key/value pair.
     *
     * @param mixed $replace
     * @param mixed $key
     * @param mixed $value
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function replace($replace, $key, $value): self
    {
        $that = $this->remove($replace);

        return $that->set($key, $value);
    }

    /**
     * Create an array using the current array as values and the other array as keys.
     *
     * @param array $keys <p>An array of keys.</p>
     *
     * @return static
     *                <p>(Immutable) Arrayy object with keys from the other array.</p>
     */
    public function replaceAllKeys(array $keys): self
    {
        return static::create(
            \array_combine($keys, $this->getArray()),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Create an array using the current array as keys and the other array as values.
     *
     * @param array $array <p>An array o values.</p>
     *
     * @return static
     *                <p>(Immutable) Arrayy object with values from the other array.</p>
     */
    public function replaceAllValues(array $array): self
    {
        return static::create(
            \array_combine($this->array, $array),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Replace the keys in an array with another set.
     *
     * @param array $keys <p>An array of keys matching the array's size</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function replaceKeys(array $keys): self
    {
        $values = \array_values($this->getArray());
        $result = \array_combine($keys, $values);

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Replace the first matched value in an array.
     *
     * @param mixed $search      <p>The value to replace.</p>
     * @param mixed $replacement <p>The value to replace.</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function replaceOneValue($search, $replacement = ''): self
    {
        $array = $this->getArray();
        $key = \array_search($search, $array, true);

        if ($key !== false) {
            $array[$key] = $replacement;
        }

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Replace values in the current array.
     *
     * @param mixed $search      <p>The value to replace.</p>
     * @param mixed $replacement <p>What to replace it with.</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function replaceValues($search, $replacement = ''): self
    {
        $array = $this->each(
            static function ($value) use ($search, $replacement) {
                return \str_replace($search, $replacement, $value);
            }
        );

        return $array;
    }

    /**
     * Get the last elements from index $from until the end of this array.
     *
     * @param int $from
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function rest(int $from = 1): self
    {
        $tmpArray = $this->getArray();

        return static::create(
            \array_splice($tmpArray, $from),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return the array in the reverse order.
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function reverse(): self
    {
        $this->generatorToArray();

        $this->array = \array_reverse($this->array);

        return $this;
    }

    /**
     * Sort an array in reverse order.
     *
     * @param int $sort_flags [optional] <p>
     *                        You may modify the behavior of the sort using the optional
     *                        parameter sort_flags, for details
     *                        see sort.
     *                        </p>
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function rsort(int $sort_flags = 0): self
    {
        $this->generatorToArray();

        \rsort($this->array, $sort_flags);

        return $this;
    }

    /**
     * Search for the first index of the current array via $value.
     *
     * @param mixed $value
     *
     * @return false|float|int|string
     *                                <p>Will return <b>FALSE</b> if the value can't be found.</p>
     */
    public function searchIndex($value)
    {
        foreach ($this->getGenerator() as $keyFromArray => $valueFromArray) {
            if ($value === $valueFromArray) {
                return $keyFromArray;
            }
        }

        return false;
    }

    /**
     * Search for the value of the current array via $index.
     *
     * @param mixed $index
     *
     * @return static
     *                <p>(Immutable) Will return a empty Arrayy if the value wasn't found.</p>
     */
    public function searchValue($index): self
    {
        $this->generatorToArray();

        // init
        $return = [];

        if ($this->isEmpty()) {
            return static::create(
                [],
                $this->iteratorClass,
                false
            );
        }

        // php cast "bool"-index into "int"-index
        if ((bool) $index === $index) {
            $index = (int) $index;
        }

        if (\array_key_exists($index, $this->array) === true) {
            $return = [$this->array[$index]];
        }

        return static::create(
            $return,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Set a value for the current array (optional using dot-notation).
     *
     * @param string $key   <p>The key to set.</p>
     * @param mixed  $value <p>Its value.</p>
     *
     * @return static
     *                <p>(Mutable)</p>
     */
    public function set($key, $value): self
    {
        $this->generatorToArray();

        $this->internalSet($key, $value);

        return $this;
    }

    /**
     * Get a value from a array and set it if it was not.
     *
     * WARNING: this method only set the value, if the $key is not already set
     *
     * @param mixed $key      <p>The key</p>
     * @param mixed $fallback <p>The default value to set if it isn't.</p>
     *
     * @return mixed
     *               <p>(Mutable)</p>
     */
    public function setAndGet($key, $fallback = null)
    {
        $this->generatorToArray();

        // If the key doesn't exist, set it.
        if (!$this->has($key)) {
            $this->array = $this->set($key, $fallback)->getArray();
        }

        return $this->get($key);
    }

    /**
     * Shifts a specified value off the beginning of array.
     *
     * @return mixed
     *               <p>(Mutable) A shifted element from the current array.</p>
     */
    public function shift()
    {
        $this->generatorToArray();

        return \array_shift($this->array);
    }

    /**
     * Shuffle the current array.
     *
     * @param bool  $secure <p>using a CSPRNG | @link https://paragonie.com/b/JvICXzh_jhLyt4y3</p>
     * @param array $array  [optional]
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function shuffle(bool $secure = false, array $array = null): self
    {
        if ($array === null) {
            $array = $this->getArray();
        }

        if ($secure !== true) {
            /** @noinspection NonSecureShuffleUsageInspection */
            \shuffle($array);
        } else {
            $size = \count($array, \COUNT_NORMAL);
            $keys = \array_keys($array);
            for ($i = $size - 1; $i > 0; --$i) {
                try {
                    $r = \random_int(0, $i);
                } catch (\Exception $e) {
                    /** @noinspection RandomApiMigrationInspection */
                    $r = \mt_rand(0, $i);
                }
                if ($r !== $i) {
                    $temp = $array[$keys[$r]];
                    $array[$keys[$r]] = $array[$keys[$i]];
                    $array[$keys[$i]] = $temp;
                }
            }

            // reset indices
            $array = \array_values($array);
        }

        foreach ($array as $key => $value) {
            // check if recursive is needed
            if (\is_array($value) === true) {
                $array[$key] = $this->shuffle($secure, $value);
            }
        }

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Count the values from the current array.
     *
     * alias: for "Arrayy->count()"
     *
     * @param int $mode
     *
     * @return int
     */
    public function size(int $mode = \COUNT_NORMAL): int
    {
        return $this->count($mode);
    }

    /**
     * Counts all elements in an array, or something in an object.
     *
     * <p>
     * For objects, if you have SPL installed, you can hook into count() by implementing interface {@see Countable}.
     * The interface has exactly one method, {@see Countable::count()}, which returns the return value for the count()
     * function. Please see the {@see Array} section of the manual for a detailed explanation of how arrays are
     * implemented and used in PHP.
     * </p>
     *
     * @return int
     *             <p>
     *             The number of elements in var, which is
     *             typically an array, since anything else will have one
     *             element.
     *             </p>
     *             <p>
     *             If var is not an array or an object with
     *             implemented Countable interface,
     *             1 will be returned.
     *             There is one exception, if var is &null;,
     *             0 will be returned.
     *             </p>
     *             <p>
     *             Caution: count may return 0 for a variable that isn't set,
     *             but it may also return 0 for a variable that has been initialized with an
     *             empty array. Use isset to test if a variable is set.
     *             </p>
     */
    public function sizeRecursive(): int
    {
        return \count($this->getArray(), \COUNT_RECURSIVE);
    }

    /**
     * Extract a slice of the array.
     *
     * @param int      $offset       <p>Slice begin index.</p>
     * @param int|null $length       <p>Length of the slice.</p>
     * @param bool     $preserveKeys <p>Whether array keys are preserved or no.</p>
     *
     * @return static
     *                <p>A slice of the original array with length $length.</p>
     */
    public function slice(int $offset, int $length = null, bool $preserveKeys = false): self
    {
        return static::create(
            \array_slice(
                $this->getArray(),
                $offset,
                $length,
                $preserveKeys
            ),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Sort the current array and optional you can keep the keys.
     *
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>sort_flags => use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     * @param bool       $keepKeys
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function sort($direction = \SORT_ASC, int $strategy = \SORT_REGULAR, bool $keepKeys = false): self
    {
        $this->generatorToArray();

        return $this->sorting(
            $this->array,
            $direction,
            $strategy,
            $keepKeys
        );
    }

    /**
     * Sort the current array by key.
     *
     * @see http://php.net/manual/en/function.ksort.php
     * @see http://php.net/manual/en/function.krsort.php
     *
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    public function sortKeys($direction = \SORT_ASC, int $strategy = \SORT_REGULAR): self
    {
        $this->generatorToArray();

        $this->sorterKeys($this->array, $direction, $strategy);

        return $this;
    }

    /**
     * Sort the current array by value.
     *
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     *
     * @return static
     *                <p>(Mutable)</p>
     */
    public function sortValueKeepIndex($direction = \SORT_ASC, int $strategy = \SORT_REGULAR): self
    {
        return $this->sort($direction, $strategy, true);
    }

    /**
     * Sort the current array by value.
     *
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     *
     * @return static
     *                <p>(Mutable)</p>
     */
    public function sortValueNewIndex($direction = \SORT_ASC, int $strategy = \SORT_REGULAR): self
    {
        return $this->sort($direction, $strategy, false);
    }

    /**
     * Sort a array by value, by a closure or by a property.
     *
     * - If the sorter is null, the array is sorted naturally.
     * - Associative (string) keys will be maintained, but numeric keys will be re-indexed.
     *
     * @param \callable|null $sorter
     * @param int|string     $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int            $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                                  <strong>SORT_NATURAL</strong></p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function sorter($sorter = null, $direction = \SORT_ASC, int $strategy = \SORT_REGULAR): self
    {
        $array = $this->getArray();
        $direction = $this->getDirection($direction);

        // Transform all values into their results.
        if ($sorter) {
            $arrayy = static::create(
                $array,
                $this->iteratorClass,
                false
            );

            $that = $this;
            $results = $arrayy->each(
                static function ($value) use ($sorter, $that) {
                    return \is_callable($sorter) ? $sorter($value) : $that->get($sorter, null, $value);
                }
            );

            $results = $results->getArray();
        } else {
            $results = $array;
        }

        // Sort by the results and replace by original values
        \array_multisort($results, $direction, $strategy, $array);

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Split an array in the given amount of pieces.
     *
     * @param int  $numberOfPieces
     * @param bool $keepKeys
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function split(int $numberOfPieces = 2, bool $keepKeys = false): self
    {
        $this->generatorToArray();

        $arrayCount = \count($this->array, \COUNT_NORMAL);

        if ($arrayCount === 0) {
            $result = [];
        } else {
            $splitSize = (int) \ceil($arrayCount / $numberOfPieces);
            $result = \array_chunk($this->array, $splitSize, $keepKeys);
        }

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Stripe all empty items.
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function stripEmpty(): self
    {
        return $this->filter(
            static function ($item) {
                if ($item === null) {
                    return false;
                }

                return (bool) \trim((string) $item);
            }
        );
    }

    /**
     * Swap two values between positions by key.
     *
     * @param int|string $swapA <p>a key in the array</p>
     * @param int|string $swapB <p>a key in the array</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function swap($swapA, $swapB): self
    {
        $array = $this->getArray();

        list($array[$swapA], $array[$swapB]) = [$array[$swapB], $array[$swapA]];

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * alias: for "Arrayy->getArray()"
     *
     * @see Arrayy::getArray()
     */
    public function toArray()
    {
        return $this->getArray();
    }

    /**
     * Convert the current array to JSON.
     *
     * @param int $options [optional] <p>e.g. JSON_PRETTY_PRINT</p>
     * @param int $depth   [optional] <p>Set the maximum depth. Must be greater than zero.</p>
     *
     * @return string
     */
    public function toJson(int $options = 0, int $depth = 512): string
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        return \json_encode($this->getArray(), $options, $depth);
    }

    /**
     * Implodes array to a string with specified separator.
     *
     * @param string $separator [optional] <p>The element's separator.</p>
     *
     * @return string
     *                <p>The string representation of array, separated by ",".</p>
     */
    public function toString(string $separator = ','): string
    {
        return $this->implode($separator);
    }

    /**
     * Return a duplicate free copy of the current array.
     *
     * @return static
     *                <p>(Mutable)</p>
     */
    public function unique(): self
    {
        // INFO: \array_unique() can't handle e.g. "stdClass"-values in an array

        $this->array = $this->reduce(
            static function ($resultArray, $value) {
                if (!\in_array($value, $resultArray, true)) {
                    $resultArray[] = $value;
                }

                return $resultArray;
            },
            []
        );
        $this->generator = null;

        return $this;
    }

    /**
     * Return a duplicate free copy of the current array. (with the old keys)
     *
     * @return static
     *                <p>(Mutable)</p>
     */
    public function uniqueKeepIndex(): self
    {
        // INFO: \array_unique() can't handle e.g. "stdClass"-values in an array

        // init
        $array = $this->getArray();

        $this->array = \array_reduce(
            \array_keys($array),
            static function ($resultArray, $key) use ($array) {
                if (!\in_array($array[$key], $resultArray, true)) {
                    $resultArray[$key] = $array[$key];
                }

                return $resultArray;
            },
            []
        );
        $this->generator = null;

        if ($this->array === null) {
            $this->array = [];
        } else {
            $this->array = (array) $this->array;
        }

        return $this;
    }

    /**
     * alias: for "Arrayy->unique()"
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     *
     * @see Arrayy::unique()
     */
    public function uniqueNewIndex(): self
    {
        return $this->unique();
    }

    /**
     * Prepends one or more values to the beginning of array at once.
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with prepended elements to the beginning of array.</p>
     */
    public function unshift(/* variadic arguments allowed */): self
    {
        $this->generatorToArray();

        if (\func_num_args()) {
            $args = \array_merge([&$this->array], \func_get_args());
            \array_unshift(...$args);
        }

        return $this;
    }

    /**
     * Get all values from a array.
     *
     * @return static
     *                <p>(Immutable)</p>
     */
    public function values(): self
    {
        return static::create(
            \array_values($this->getArray()),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Apply the given function to every element in the array, discarding the results.
     *
     * @param \callable $callable
     * @param bool      $recursive <p>Whether array will be walked recursively or no</p>
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with modified elements.</p>
     */
    public function walk($callable, bool $recursive = false): self
    {
        $this->generatorToArray();

        if ($recursive === true) {
            \array_walk_recursive($this->array, $callable);
        } else {
            \array_walk($this->array, $callable);
        }

        return $this;
    }

    /**
     * Convert an array into a object.
     *
     * @param array $array PHP array
     *
     * @return \stdClass
     */
    protected static function arrayToObject(array $array = []): \stdClass
    {
        // init
        $object = new \stdClass();

        if (\count($array, \COUNT_NORMAL) <= 0) {
            return $object;
        }

        foreach ($array as $name => $value) {
            if (\is_array($value)) {
                $object->{$name} = self::arrayToObject($value);
            } else {
                $object->{$name} = $value;
            }
        }

        return $object;
    }

    /**
     * @param array|\Generator|null $input        <p>
     *                                            An array containing keys to return.
     *                                            </p>
     * @param mixed                 $search_value [optional] <p>
     *                                            If specified, then only keys containing these values are returned.
     *                                            </p>
     * @param bool                  $strict       [optional] <p>
     *                                            Determines if strict comparison (===) should be used during the
     *                                            search.
     *                                            </p>
     *
     * @return array
     *               <p>an array of all the keys in input</p>
     */
    protected function array_keys_recursive(
        $input = null,
        $search_value = null,
        bool $strict = true
    ): array {
        // init
        $keys = [];
        $keysTmp = [[]]; // the inner empty array covers cases when no loops were made

        if ($input === null) {
            $input = $this->getGenerator();
        }

        foreach ($input as $key => $value) {
            if (
                $search_value === null
                ||
                (
                    \is_array($search_value) === true
                    &&
                    \in_array($key, $search_value, $strict)
                )
            ) {
                $keys[] = $key;
            }

            // check if recursive is needed
            if (\is_array($value) === true) {
                $keysTmp[] = $this->array_keys_recursive($value);
            }
        }

        return \array_merge($keys, ...$keysTmp);
    }

    /**
     * @param mixed      $path
     * @param \callable  $callable
     * @param array|null $currentOffset
     */
    protected function callAtPath($path, $callable, &$currentOffset = null)
    {
        $this->generatorToArray();

        if ($currentOffset === null) {
            $currentOffset = &$this->array;
        }

        $explodedPath = \explode($this->pathSeparator, $path);
        $nextPath = \array_shift($explodedPath);

        if (!isset($currentOffset[$nextPath])) {
            return;
        }

        if (!empty($explodedPath)) {
            $this->callAtPath(
                \implode($this->pathSeparator, $explodedPath),
                $callable,
                $currentOffset[$nextPath]
            );
        } else {
            $callable($currentOffset[$nextPath]);
        }
    }

    /**
     * create a fallback for array
     *
     * 1. use the current array, if it's a array
     * 2. fallback to empty array, if there is nothing
     * 3. call "getArray()" on object, if there is a "Arrayy"-object
     * 4. call "createFromObject()" on object, if there is a "\ArrayAccess"-object
     * 5. call "__toArray()" on object, if the method exists
     * 6. cast a string or object with "__toString()" into an array
     * 7. throw a "InvalidArgumentException"-Exception
     *
     * @param mixed $array
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function fallbackForArray(&$array): array
    {
        if (\is_array($array)) {
            return $array;
        }

        if (!$array) {
            return [];
        }

        $isObject = \is_object($array);

        if ($isObject && $array instanceof self) {
            return $array->getArray();
        }

        if ($isObject && $array instanceof \ArrayAccess) {
            return static::createFromObject($array)->getArray();
        }

        if ($isObject && $array instanceof \ArrayObject) {
            return $array->getArrayCopy();
        }

        if ($isObject && $array instanceof \Generator) {
            return static::createFromGeneratorImmutable($array)->getArray();
        }

        if (\is_callable($array)) {
            $this->generator = new ArrayyRewindableGenerator($array);

            return [];
        }

        if ($isObject && \method_exists($array, '__toArray')) {
            return (array) $array->__toArray();
        }

        if (
            \is_string($array)
            ||
            ($isObject && \method_exists($array, '__toString'))
        ) {
            return [(string) $array];
        }

        throw new \InvalidArgumentException(
            'Passed value should be a array'
        );
    }

    /**
     * Get correct PHP constant for direction.
     *
     * @param int|string $direction
     *
     * @return int
     */
    protected function getDirection($direction): int
    {
        if (\is_string($direction)) {
            $direction = \strtolower($direction);

            if ($direction === 'desc') {
                $direction = \SORT_DESC;
            } else {
                $direction = \SORT_ASC;
            }
        }

        if (
            $direction !== \SORT_DESC
            &&
            $direction !== \SORT_ASC
        ) {
            $direction = \SORT_ASC;
        }

        return $direction;
    }

    /**
     * @param mixed               $glue
     * @param array|static|string $pieces
     * @param bool                $useKeys
     *
     * @return string
     */
    protected function implode_recursive($glue = '', $pieces = [], bool $useKeys = false): string
    {
        if ($pieces instanceof self) {
            $pieces = $pieces->getArray();
        }

        if (\is_array($pieces)) {
            $pieces_count = \count($pieces, \COUNT_NORMAL);
            $pieces_count_not_zero = $pieces_count > 0;

            return \implode(
                $glue,
                \array_map(
                    [$this, 'implode_recursive'],
                    \array_fill(0, ($pieces_count_not_zero ? $pieces_count : 1), $glue),
                    ($useKeys === true && $pieces_count_not_zero ? $this->array_keys_recursive($pieces) : $pieces)
                )
            );
        }

        return (string) $pieces;
    }

    /**
     * @param mixed                 $needle   <p>
     *                                        The searched value.
     *                                        </p>
     *                                        <p>
     *                                        If needle is a string, the comparison is done
     *                                        in a case-sensitive manner.
     *                                        </p>
     * @param array|\Generator|null $haystack <p>
     *                                        The array.
     *                                        </p>
     * @param bool                  $strict   [optional] <p>
     *                                        If the third parameter strict is set to true
     *                                        then the in_array function will also check the
     *                                        types of the
     *                                        needle in the haystack.
     *                                        </p>
     *
     * @return bool
     *              <p>true if needle is found in the array, false otherwise</p>
     */
    protected function in_array_recursive($needle, $haystack = null, $strict = true): bool
    {
        if ($haystack === null) {
            $haystack = $this->getGenerator();
        }

        foreach ($haystack as $item) {
            if (\is_array($item) === true) {
                $returnTmp = $this->in_array_recursive($needle, $item, $strict);
            } else {
                $returnTmp = ($strict === true ? $item === $needle : $item == $needle);
            }

            if ($returnTmp === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $value
     */
    protected function internalGetArray(&$value)
    {
        if ($value instanceof self) {
            $valueTmp = $value->getArray();
            if (\count($valueTmp, \COUNT_NORMAL) === 0) {
                $value = [];
            } else {
                /** @noinspection PhpUnusedLocalVariableInspection */
                $value = &$valueTmp;
            }
        }

        /** @noinspection PhpComposerExtensionStubsInspection */
        /** @noinspection NotOptimalIfConditionsInspection */
        if (
            \class_exists('JsonSerializable')
            &&
            $value instanceof \JsonSerializable
        ) {

            /** @noinspection PhpUnusedLocalVariableInspection */
            $value = &$value->jsonSerialize();
        }
    }

    /**
     * Internal mechanics of remove method.
     *
     * @param mixed $key
     *
     * @return bool
     */
    protected function internalRemove($key): bool
    {
        $this->generatorToArray();

        $path = \explode($this->pathSeparator, (string) $key);

        // Crawl though the keys
        while (\count($path, \COUNT_NORMAL) > 1) {
            $key = \array_shift($path);

            if (!$this->has($key)) {
                return false;
            }

            $this->array = &$this->array[$key];
        }

        $key = \array_shift($path);

        unset($this->array[$key]);

        return true;
    }

    /**
     * Internal mechanic of set method.
     *
     * @param string|null $key
     * @param mixed       $value
     * @param bool        $checkProperties
     *
     * @return bool
     */
    protected function internalSet($key, $value, $checkProperties = true): bool
    {
        if (
            $checkProperties === true
            &&
            $this->properties !== []
        ) {
            if (isset($this->properties[$key]) === false) {
                throw new \InvalidArgumentException('The key ' . $key . ' does not exists as @property in the class (' . \get_class($this) . ').');
            }

            $this->properties[$key]->checkType($value);
        }

        if ($key === null) {
            return false;
        }

        $this->generatorToArray();

        // init
        $array = &$this->array;
        $path = \explode($this->pathSeparator, (string) $key);

        // Crawl through the keys
        while (\count($path, \COUNT_NORMAL) > 1) {
            $key = \array_shift($path);

            $array = &$array[$key];
        }

        $array[\array_shift($path)] = $value;

        return true;
    }

    /**
     * Convert a object into an array.
     *
     * @param object $object
     *
     * @return mixed
     */
    protected static function objectToArray($object)
    {
        if (!\is_object($object)) {
            return $object;
        }

        if (\is_object($object)) {
            $object = \get_object_vars($object);
        }

        return \array_map(['self', 'objectToArray'], $object);
    }

    /**
     * sorting keys
     *
     * @param array      $elements
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    protected function sorterKeys(array &$elements, $direction = \SORT_ASC, int $strategy = \SORT_REGULAR): self
    {
        $direction = $this->getDirection($direction);

        switch ($direction) {
            case 'desc':
            case \SORT_DESC:
                \krsort($elements, $strategy);

                break;
            case 'asc':
            case \SORT_ASC:
            default:
                \ksort($elements, $strategy);
        }

        return $this;
    }

    /**
     * @param array      $elements  <p>Warning: used as reference</p>
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     * @param bool       $keepKeys
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     */
    protected function sorting(array &$elements, $direction = \SORT_ASC, int $strategy = \SORT_REGULAR, bool $keepKeys = false): self
    {
        $direction = $this->getDirection($direction);

        if (!$strategy) {
            $strategy = \SORT_REGULAR;
        }

        switch ($direction) {
            case 'desc':
            case \SORT_DESC:
                if ($keepKeys) {
                    \arsort($elements, $strategy);
                } else {
                    \rsort($elements, $strategy);
                }

                break;
            case 'asc':
            case \SORT_ASC:
            default:
                if ($keepKeys) {
                    \asort($elements, $strategy);
                } else {
                    \sort($elements, $strategy);
                }
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function generatorToArray(): bool
    {
        if ($this->generator) {
            $this->array = $this->getArray();
            $this->generator = null;

            return true;
        }

        return false;
    }

    /**
     * @return array|Property[]
     */
    private function getPropertiesFromPhpDoc(): array
    {
        static $PROPERTY_CACHE = [];
        $cacheKey = 'Class::' . static::class;

        if (isset($PROPERTY_CACHE[$cacheKey])) {
            return $PROPERTY_CACHE[$cacheKey];
        }

        // init
        $properties = [];

        $reflector = new \ReflectionClass($this);
        $factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $docblock = $factory->create($reflector->getDocComment());
        foreach ($docblock->getTagsByName('property') as $tag) {
            /* @var $tag \phpDocumentor\Reflection\DocBlock\Tags\Property */
            $properties[$tag->getVariableName()] = Property::fromPhpDocumentorProperty($tag);
        }

        return $PROPERTY_CACHE[$cacheKey] = $properties;
    }
}
