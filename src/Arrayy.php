<?php

declare(strict_types=1);

namespace Arrayy;

use Arrayy\Type\TypeInterface;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckInterface;
use Arrayy\TypeCheck\TypeCheckPhpDoc;

/**
 * Methods to manage arrays.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @template TKey of array-key
 * @template T
 * @template-extends \ArrayObject<TKey,T>
 * @template-implements \IteratorAggregate<TKey,T>
 * @template-implements \ArrayAccess<TKey,T>
 */
class Arrayy extends \ArrayObject implements \IteratorAggregate, \ArrayAccess, \Serializable, \JsonSerializable, \Countable
{
    const ARRAYY_HELPER_TYPES_FOR_ALL_PROPERTIES = '!!!!Arrayy_Helper_Types_For_All_Properties!!!!';

    const ARRAYY_HELPER_WALK = '!!!!Arrayy_Helper_Walk!!!!';

    /**
     * @var array
     *
     * @phpstan-var array<int|string|TKey,T>
     */
    protected $array = [];

    /**
     * @var \Arrayy\ArrayyRewindableGenerator|null
     *
     * @phpstan-var \Arrayy\ArrayyRewindableGenerator<TKey,T>|null
     */
    protected $generator;

    /**
     * @var string
     *
     * @phpstan-var class-string<\Arrayy\ArrayyIterator>
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
     * @var bool
     */
    protected $checkPropertiesMismatch = true;

    /**
     * @var array<int|string,TypeCheckInterface>|mixed|TypeCheckArray<int|string,TypeCheckInterface>|TypeInterface
     */
    protected $properties = [];

    /**
     * Initializes
     *
     * @param mixed  $data                         <p>
     *                                             Should be an array or a generator, otherwise it will try
     *                                             to convert it into an array.
     *                                             </p>
     * @param string $iteratorClass                optional <p>
     *                                             You can overwrite the ArrayyIterator, but mostly you don't
     *                                             need this option.
     *                                             </p>
     * @param bool   $checkPropertiesInConstructor optional <p>
     *                                             You need to extend the "Arrayy"-class and you need to set
     *                                             the $checkPropertiesMismatchInConstructor class property
     *                                             to
     *                                             true, otherwise this option didn't not work anyway.
     *                                             </p>
     *
     * @phpstan-param class-string<\Arrayy\ArrayyIterator> $iteratorClass
     */
    public function __construct(
        $data = [],
        string $iteratorClass = ArrayyIterator::class,
        bool $checkPropertiesInConstructor = true
    ) {
        $data = $this->fallbackForArray($data);

        // used only for serialize + unserialize, all other methods are overwritten
        /**
         * @psalm-suppress InvalidArgument - why?
         */
        parent::__construct([], 0, $iteratorClass);

        $this->setInitialValuesAndProperties($data, $checkPropertiesInConstructor);

        $this->setIteratorClass($iteratorClass);
    }

    /**
     * @return void
     */
    public function __clone()
    {
        if (!\is_array($this->properties)) {
            $this->properties = clone $this->properties;
        }

        if ($this->generator !== null) {
            $this->generator = clone $this->generator;
        }
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

        return $this->toArray();
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
     *
     * @return void
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
        $return = $this->get($key, null, null, true);

        if (\is_array($return) === true) {
            $return = static::create(
                [],
                $this->iteratorClass,
                false
            )->createByReference($return);
        }

        return $return;
    }

    /**
     * Add new values (optional using dot-notation).
     *
     * @param mixed           $value
     * @param int|string|null $key
     *
     * @return static
     *                <p>(Immutable) Return this Arrayy object, with the appended values.</p>
     *
     * @phpstan-param  T $value
     * @phpstan-return static<TKey,T>
     *
     * @phpstan-param T $value
     * @phpstan-param TKey $key
     * @psalm-mutation-free
     */
    public function add($value, $key = null)
    {
        if ($key !== null) {
            $get = $this->get($key);
            if ($get !== null) {
                $value = \array_merge_recursive(
                    !$get instanceof self ? [$get] : $get->getArray(),
                    !\is_array($value) ? [$value] : $value
                );
            }

            $this->internalSet($key, $value);

            return $this;
        }

        return $this->append($value);
    }

    /**
     * Append a (key) + value to the current array.
     *
     * EXAMPLE: <code>
     * a(['fòô' => 'bàř'])->append('foo'); // Arrayy['fòô' => 'bàř', 0 => 'foo']
     * </code>
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     *
     * @phpstan-param T $value
     * @phpstan-param TKey|null $key
     * @phpstan-return static<TKey,T>
     */
    public function append($value, $key = null): self
    {
        $this->generatorToArray();

        if ($this->properties !== []) {
            $this->checkType($key, $value);
        }

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
     * Append a (key) + value to the current array.
     *
     * EXAMPLE: <code>
     * a(['fòô' => 'bàř'])->appendImmutable('foo')->getArray(); // ['fòô' => 'bàř', 0 => 'foo']
     * </code>
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object, with the appended values.</p>
     *
     * @phpstan-param T $value
     * @phpstan-param TKey $key
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function appendImmutable($value, $key = null): self
    {
        $generator = function () use ($key, $value): \Generator {
            if ($this->properties !== []) {
                $this->checkType($key, $value);
            }

            /** @noinspection YieldFromCanBeUsedInspection - FP */
            foreach ($this->getGenerator() as $keyOld => $itemOld) {
                yield $keyOld => $itemOld;
            }

            if ($key !== null) {
                yield $key => $value;
            } else {
                yield $value;
            }
        };

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
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
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function asort(int $sort_flags = 0): self
    {
        $this->generatorToArray();

        \asort($this->array, $sort_flags);

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
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function asortImmutable(int $sort_flags = 0): self
    {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        $that->asort($sort_flags);

        return $that;
    }

    /**
     * Counts all elements in an array, or something in an object.
     *
     * EXAMPLE: <code>
     * a([-9, -8, -7, 1.32])->count(); // 4
     * </code>
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
     * @psalm-mutation-free
     */
    public function count(int $mode = \COUNT_NORMAL): int
    {
        if (
            $this->generator
            &&
            $mode === \COUNT_NORMAL
        ) {
            return \iterator_count($this->generator);
        }

        return \count($this->toArray(), $mode);
    }

    /**
     * Exchange the array for another one.
     *
     * @param array|mixed|static $data
     *
     * 1. use the current array, if it's a array
     * 2. fallback to empty array, if there is nothing
     * 3. call "getArray()" on object, if there is a "Arrayy"-object
     * 4. call "createFromObject()" on object, if there is a "\Traversable"-object
     * 5. call "__toArray()" on object, if the method exists
     * 6. cast a string or object with "__toString()" into an array
     * 7. throw a "InvalidArgumentException"-Exception
     *
     * @return array
     *
     * @phpstan-param  T|array<TKey,T>|self<TKey,T> $data
     * @phpstan-return array<TKey,T>
     */
    public function exchangeArray($data): array
    {
        /** @phpstan-var array<TKey,T> array */
        $array = $this->fallbackForArray($data);

        $this->array = $array;
        $this->generator = null;

        return $this->array;
    }

    /**
     * Creates a copy of the ArrayyObject.
     *
     * @return array
     *
     * @phpstan-return array<int|string|TKey,T>
     */
    public function getArrayCopy(): array
    {
        $this->generatorToArray();

        return $this->array;
    }

    /**
     * Returns a new iterator, thus implementing the \Iterator interface.
     *
     * EXAMPLE: <code>
     * a(['foo', 'bar'])->getIterator(); // ArrayyIterator['foo', 'bar']
     * </code>
     *
     * @return \Iterator<mixed, mixed>
     *                          <p>An iterator for the values in the array.</p>
     * @phpstan-return \Iterator<array-key|TKey, mixed|T>
     */
    public function getIterator(): \Iterator
    {
        if ($this->generator instanceof ArrayyRewindableGenerator) {
            $generator = clone $this->generator;
            $this->generator = new ArrayyRewindableExtendedGenerator(
                static function () use ($generator): \Generator {
                    yield from $generator;
                },
                null,
                static::class
            );

            return $this->generator;
        }

        $iterator = $this->getIteratorClass();

        if ($iterator === ArrayyIterator::class) {
            return new $iterator($this->toArray(), 0, static::class);
        }

        $return = new $iterator($this->toArray());
        \assert($return instanceof \Iterator);

        return $return;
    }

    /**
     * Gets the iterator classname for the ArrayObject.
     *
     * @return string
     *
     * @phpstan-return class-string
     */
    public function getIteratorClass(): string
    {
        return $this->iteratorClass;
    }

    /**
     * Sort the entries by key.
     *
     * @param int $sort_flags [optional] <p>
     *                        You may modify the behavior of the sort using the optional
     *                        parameter sort_flags, for details
     *                        see sort.
     *                        </p>
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function ksort(int $sort_flags = 0): self
    {
        $this->generatorToArray();

        \ksort($this->array, $sort_flags);

        return $this;
    }

    /**
     * Sort the entries by key.
     *
     * @param int $sort_flags [optional] <p>
     *                        You may modify the behavior of the sort using the optional
     *                        parameter sort_flags, for details
     *                        see sort.
     *                        </p>
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function ksortImmutable(int $sort_flags = 0): self
    {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        $that->ksort($sort_flags);

        return $that;
    }

    /**
     * Sort an array using a case insensitive "natural order" algorithm.
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function natcasesort(): self
    {
        $this->generatorToArray();

        \natcasesort($this->array);

        return $this;
    }

    /**
     * Sort an array using a case insensitive "natural order" algorithm.
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function natcasesortImmutable(): self
    {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        $that->natcasesort();

        return $that;
    }

    /**
     * Sort entries using a "natural order" algorithm.
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function natsort(): self
    {
        $this->generatorToArray();

        \natsort($this->array);

        return $this;
    }

    /**
     * Sort entries using a "natural order" algorithm.
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function natsortImmutable(): self
    {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        $that->natsort();

        return $that;
    }

    /**
     * Whether or not an offset exists.
     *
     * @param bool|int|string $offset
     *
     * @return bool
     *
     * @psalm-mutation-free
     */
    public function offsetExists($offset): bool
    {
        // php cast "bool"-index into "int"-index
        if ((bool) $offset === $offset) {
            $offset = (int) $offset;
        }
        \assert(\is_int($offset) || \is_string($offset));

        $offsetExists = $this->keyExists($offset);
        if ($offsetExists === true) {
            return true;
        }

        /**
         * https://github.com/vimeo/psalm/issues/2536
         *
         * @psalm-suppress PossiblyInvalidArgument
         * @psalm-suppress InvalidScalarArgument
         */
        if (
            $this->pathSeparator
            &&
            (string) $offset === $offset
            &&
            \strpos($offset, $this->pathSeparator) !== false
        ) {
            $explodedPath = \explode($this->pathSeparator, (string) $offset);
            if ($explodedPath !== false) {
                /** @var string $lastOffset - helper for phpstan */
                $lastOffset = \array_pop($explodedPath);
                $containerPath = \implode($this->pathSeparator, $explodedPath);

                /**
                 * @psalm-suppress MissingClosureReturnType
                 * @psalm-suppress MissingClosureParamType
                 */
                $this->callAtPath(
                    $containerPath,
                    static function ($container) use ($lastOffset, &$offsetExists) {
                        $offsetExists = \array_key_exists($lastOffset, $container);
                    }
                );
            }
        }

        return $offsetExists;
    }

    /**
     * Returns the value at specified offset.
     *
     * @param int|string $offset
     *
     * @return mixed
     *               <p>Will return null if the offset did not exists.</p>
     */
    public function &offsetGet($offset)
    {
        // init
        $value = null;

        if ($this->offsetExists($offset)) {
            $value = &$this->__get($offset);
        }

        return $value;
    }

    /**
     * Assigns a value to the specified offset + check the type.
     *
     * @param int|string|null $offset
     * @param mixed           $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->generatorToArray();

        if ($offset === null) {
            if ($this->properties !== []) {
                $this->checkType(null, $value);
            }

            $this->array[] = $value;
        } else {
            $this->internalSet(
                $offset,
                $value,
                true
            );
        }
    }

    /**
     * Unset an offset.
     *
     * @param int|string $offset
     *
     * @return void
     *              <p>(Mutable) Return nothing.</p>
     */
    public function offsetUnset($offset)
    {
        $this->generatorToArray();

        if ($this->array === []) {
            return;
        }

        if ($this->keyExists($offset)) {
            unset($this->array[$offset]);

            return;
        }

        /**
         * https://github.com/vimeo/psalm/issues/2536
         *
         * @psalm-suppress PossiblyInvalidArgument
         * @psalm-suppress InvalidScalarArgument
         */
        if (
            $this->pathSeparator
            &&
            (string) $offset === $offset
            &&
            \strpos($offset, $this->pathSeparator) !== false
        ) {
            $path = \explode($this->pathSeparator, (string) $offset);

            if ($path !== false) {
                $pathToUnset = \array_pop($path);

                /**
                 * @psalm-suppress MissingClosureReturnType
                 * @psalm-suppress MissingClosureParamType
                 */
                $this->callAtPath(
                    \implode($this->pathSeparator, $path),
                    static function (&$offset) use ($pathToUnset) {
                        if (\is_array($offset)) {
                            unset($offset[$pathToUnset]);
                        } else {
                            $offset = null;
                        }
                    }
                );
            }
        }

        unset($this->array[$offset]);
    }

    /**
     * Serialize the current "Arrayy"-object.
     *
     * EXAMPLE: <code>
     * a([1, 4, 7])->serialize();
     * </code>
     *
     * @return string
     */
    public function serialize(): string
    {
        $this->generatorToArray();

        if (\PHP_VERSION_ID < 70400) {
            return parent::serialize();
        }

        return \serialize($this);
    }

    /**
     * Sets the iterator classname for the current "Arrayy"-object.
     *
     * @param string $iteratorClass
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     *
     * @phpstan-param class-string<\Arrayy\ArrayyIterator> $iteratorClass
     */
    public function setIteratorClass($iteratorClass)
    {
        if (\class_exists($iteratorClass)) {
            $this->iteratorClass = $iteratorClass;

            return;
        }

        if (\strpos($iteratorClass, '\\') === 0) {
            /** @var class-string<\Arrayy\ArrayyIterator<TKey,T>> $iteratorClass */
            $iteratorClass = '\\' . $iteratorClass;
            if (\class_exists($iteratorClass)) {
                /**
                 * @psalm-suppress PropertyTypeCoercion
                 */
                $this->iteratorClass = $iteratorClass;

                return;
            }
        }

        throw new \InvalidArgumentException('The iterator class does not exist: ' . $iteratorClass);
    }

    /**
     * Sort the entries with a user-defined comparison function and maintain key association.
     *
     * @param callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function uasort($function): self
    {
        if (!\is_callable($function)) {
            throw new \InvalidArgumentException('Passed function must be callable');
        }

        $this->generatorToArray();

        \uasort($this->array, $function);

        return $this;
    }

    /**
     * Sort the entries with a user-defined comparison function and maintain key association.
     *
     * @param callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function uasortImmutable($function): self
    {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        $that->uasort($function);

        return $that;
    }

    /**
     * Sort the entries by keys using a user-defined comparison function.
     *
     * @param callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function uksort($function): self
    {
        return $this->customSortKeys($function);
    }

    /**
     * Sort the entries by keys using a user-defined comparison function.
     *
     * @param callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     *                <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function uksortImmutable($function): self
    {
        return $this->customSortKeysImmutable($function);
    }

    /**
     * Unserialize an string and return the instance of the "Arrayy"-class.
     *
     * EXAMPLE: <code>
     * $serialized = a([1, 4, 7])->serialize();
     * a()->unserialize($serialized);
     * </code>
     *
     * @param string $string
     *
     * @return $this
     *
     * @phpstan-return static<TKey,T>
     */
    public function unserialize($string): self
    {
        if (\PHP_VERSION_ID < 70400) {
            parent::unserialize($string);

            return $this;
        }

        return \unserialize($string, ['allowed_classes' => [__CLASS__, TypeCheckPhpDoc::class]]);
    }

    /**
     * Append a (key) + values to the current array.
     *
     * EXAMPLE: <code>
     * a(['fòô' => ['bàř']])->appendArrayValues(['foo1', 'foo2'], 'fòô'); // Arrayy['fòô' => ['bàř', 'foo1', 'foo2']]
     * </code>
     *
     * @param array $values
     * @param mixed $key
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     *
     * @phpstan-param  array<array-key,T> $values
     * @phpstan-param  TKey|null $key
     * @phpstan-return static<TKey,T>
     */
    public function appendArrayValues(array $values, $key = null)
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
     * @param int|string $prefix
     *
     * @return static
     *                <p>(Immutable) Return an Arrayy object, with the prefixed keys.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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

        return self::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Add a prefix to each value.
     *
     * @param float|int|string $prefix
     *
     * @return static
     *                <p>(Immutable) Return an Arrayy object, with the prefixed values.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
            } elseif (\is_object($item) === true) {
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
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function arsort(): self
    {
        $this->generatorToArray();

        \arsort($this->array);

        return $this;
    }

    /**
     * Sort an array in reverse order and maintain index association.
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function arsortImmutable(): self
    {
        $that = clone $this;

        $that->generatorToArray();

        \arsort($that->array);

        return $that;
    }

    /**
     * Iterate over the current array and execute a callback for each loop.
     *
     * EXAMPLE: <code>
     * $result = A::create();
     * $closure = function ($value, $key) use ($result) {
     *     $result[$key] = ':' . $value . ':';
     * };
     * a(['foo', 'bar' => 'bis'])->at($closure); // Arrayy[':foo:', 'bar' => ':bis:']
     * </code>
     *
     * @param \Closure $closure
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param \Closure(T=,TKey=):mixed $closure <p>INFO: \Closure result is not used, but void is not supported in PHP 7.0</p>
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function at(\Closure $closure): self
    {
        $that = clone $this;

        foreach ($that->getGenerator() as $key => $value) {
            $closure($value, $key);
        }

        return static::create(
            $that->toArray(),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Returns the average value of the current array.
     *
     * EXAMPLE: <code>
     * a([-9, -8, -7, 1.32])->average(2); // -5.67
     * </code>
     *
     * @param int $decimals <p>The number of decimal-numbers to return.</p>
     *
     * @return float|int
     *                   <p>The average value.</p>
     * @psalm-mutation-free
     */
    public function average($decimals = 0)
    {
        $count = \count($this->toArray(), \COUNT_NORMAL);

        if (!$count) {
            return 0;
        }

        if ((int) $decimals !== $decimals) {
            $decimals = 0;
        }

        return \round(\array_sum($this->toArray()) / $count, $decimals);
    }

    /**
     * Changes all keys in an array.
     *
     * @param int $case [optional] <p> Either <strong>CASE_UPPER</strong><br />
     *                  or <strong>CASE_LOWER</strong> (default)</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
            \assert(\is_string($key) || \is_int($key) || \is_float($key));

            if ($case === \CASE_LOWER) {
                $key = \mb_strtolower((string) $key);
            } else {
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
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function changeSeparator($separator): self
    {
        $this->pathSeparator = $separator;

        return $this;
    }

    /**
     * Create a chunked version of the current array.
     *
     * EXAMPLE: <code>
     * a([-9, -8, -7, 1.32])->chunk(2); // Arrayy[[-9, -8], [-7, 1.32]]
     * </code>
     *
     * @param int  $size         <p>Size of each chunk.</p>
     * @param bool $preserveKeys <p>Whether array keys are preserved or no.</p>
     *
     * @return static
     *                <p>(Immutable) A new array of chunks from the original array.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function chunk($size, $preserveKeys = false): self
    {
        if ($preserveKeys) {
            $generator = function () use ($size) {
                $values = [];
                $tmpCounter = 0;
                foreach ($this->getGenerator() as $key => $value) {
                    ++$tmpCounter;

                    $values[$key] = $value;
                    if ($tmpCounter === $size) {
                        yield $values;

                        $values = [];
                        $tmpCounter = 0;
                    }
                }

                if ($values !== []) {
                    yield $values;
                }
            };
        } else {
            $generator = function () use ($size) {
                $values = [];
                $tmpCounter = 0;
                foreach ($this->getGenerator() as $key => $value) {
                    ++$tmpCounter;

                    $values[] = $value;
                    if ($tmpCounter === $size) {
                        yield $values;

                        $values = [];
                        $tmpCounter = 0;
                    }
                }

                if ($values !== []) {
                    yield $values;
                }
            };
        }

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Clean all falsy values from the current array.
     *
     * EXAMPLE: <code>
     * a([-8 => -9, 1, 2 => false])->clean(); // Arrayy[-8 => -9, 1]
     * </code>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
     * WARNING!!! -> Clear the current full array or a $key of it.
     *
     * EXAMPLE: <code>
     * a([-8 => -9, 1, 2 => false])->clear(); // Arrayy[]
     * </code>
     *
     * @param int|int[]|string|string[]|null $key
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object, with an empty array.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function clear($key = null): self
    {
        if ($key !== null) {
            if (\is_array($key)) {
                foreach ($key as $keyTmp) {
                    $this->offsetUnset($keyTmp);
                }
            } else {
                $this->offsetUnset($key);
            }

            return $this;
        }

        $this->array = [];
        $this->generator = null;

        return $this;
    }

    /**
     * Check if an item is in the current array.
     *
     * EXAMPLE: <code>
     * a([1, true])->contains(true); // true
     * </code>
     *
     * @param float|int|string $value
     * @param bool             $recursive
     * @param bool             $strict
     *
     * @return bool
     * @psalm-mutation-free
     */
    public function contains($value, bool $recursive = false, bool $strict = true): bool
    {
        if ($recursive === true) {
            return $this->in_array_recursive($value, $this->toArray(), $strict);
        }

        /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
        foreach ($this->getGeneratorByReference() as &$valueFromArray) {
            if ($strict) {
                if ($value === $valueFromArray) {
                    return true;
                }
            } else {
                /** @noinspection NestedPositiveIfStatementsInspection */
                if ($value == $valueFromArray) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if an (case-insensitive) string is in the current array.
     *
     * EXAMPLE: <code>
     * a(['E', 'é'])->containsCaseInsensitive('É'); // true
     * </code>
     *
     * @param mixed $value
     * @param bool  $recursive
     *
     * @return bool
     * @psalm-mutation-free
     *
     * @psalm-suppress InvalidCast - hack for int|float|bool support
     */
    public function containsCaseInsensitive($value, $recursive = false): bool
    {
        if ($value === null) {
            return false;
        }

        if ($recursive === true) {
            /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
            foreach ($this->getGeneratorByReference() as $key => &$valueTmp) {
                if (\is_array($valueTmp)) {
                    $return = (new self($valueTmp))->containsCaseInsensitive($value, $recursive);
                    if ($return === true) {
                        return $return;
                    }
                } elseif (\mb_strtoupper((string) $valueTmp) === \mb_strtoupper((string) $value)) {
                    return true;
                }
            }

            return false;
        }

        /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
        foreach ($this->getGeneratorByReference() as $key => &$valueTmp) {
            if (\mb_strtoupper((string) $valueTmp) === \mb_strtoupper((string) $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the given key/index exists in the array.
     *
     * EXAMPLE: <code>
     * a([1 => true])->containsKey(1); // true
     * </code>
     *
     * @param int|string $key <p>key/index to search for</p>
     *
     * @return bool
     *              <p>Returns true if the given key/index exists in the array, false otherwise.</p>
     *
     * @psalm-mutation-free
     */
    public function containsKey($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Check if all given needles are present in the array as key/index.
     *
     * EXAMPLE: <code>
     * a([1 => true])->containsKeys(array(1 => 0)); // true
     * </code>
     *
     * @param array $needles   <p>The keys you are searching for.</p>
     * @param bool  $recursive
     *
     * @return bool
     *              <p>Returns true if all the given keys/indexes exists in the array, false otherwise.</p>
     *
     * @phpstan-param array<array-key>|array<TKey> $needles
     * @psalm-mutation-free
     */
    public function containsKeys(array $needles, $recursive = false): bool
    {
        if ($recursive === true) {
            return
                \count(
                    \array_intersect(
                        $needles,
                        $this->keys(true)->toArray()
                    ),
                    \COUNT_RECURSIVE
                )
                ===
                \count(
                    $needles,
                    \COUNT_RECURSIVE
                );
        }

        return \count(
            \array_intersect($needles, $this->keys()->toArray()),
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
     *
     * @phpstan-param array<array-key>|array<TKey> $needles
     * @psalm-mutation-free
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
     * @psalm-mutation-free
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
     * @psalm-mutation-free
     */
    public function containsValueRecursive($value): bool
    {
        return $this->contains($value, true);
    }

    /**
     * Check if all given needles are present in the array.
     *
     * EXAMPLE: <code>
     * a([1, true])->containsValues(array(1, true)); // true
     * </code>
     *
     * @param array $needles
     *
     * @return bool
     *              <p>Returns true if all the given values exists in the array, false otherwise.</p>
     *
     * @phpstan-param array<mixed>|array<T> $needles
     * @psalm-mutation-free
     */
    public function containsValues(array $needles): bool
    {
        return \count(
            \array_intersect(
                $needles,
                $this->toArray()
            ),
            \COUNT_NORMAL
        )
               ===
               \count(
                   $needles,
                   \COUNT_NORMAL
               );
    }

    /**
     * Counts all the values of an array
     *
     * @see          http://php.net/manual/en/function.array-count-values.php
     *
     * @return static
     *                <p>
     *                (Immutable)
     *                An associative Arrayy-object of values from input as
     *                keys and their count as value.
     *                </p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function countValues(): self
    {
        return self::create(\array_count_values($this->toArray()), $this->iteratorClass);
    }

    /**
     * Creates an Arrayy object.
     *
     * @param mixed  $data
     * @param string $iteratorClass
     * @param bool   $checkPropertiesInConstructor
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     *
     * @phpstan-param  array<array-key,T>|\Traversable<array-key,T>|callable():\Generator<TKey,T>|(T&\Traversable) $data
     * @phpstan-param  class-string<\Arrayy\ArrayyIterator> $iteratorClass
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public static function create(
        $data = [],
        string $iteratorClass = ArrayyIterator::class,
        bool $checkPropertiesInConstructor = true
    ) {
        return new static(
            $data,
            $iteratorClass,
            $checkPropertiesInConstructor
        );
    }

    /**
     * Flatten an array with the given character as a key delimiter.
     *
     * EXAMPLE: <code>
     * $dot = a(['foo' => ['abc' => 'xyz', 'bar' => ['baz']]]);
     * $flatten = $dot->flatten();
     * $flatten['foo.abc']; // 'xyz'
     * $flatten['foo.bar.0']; // 'baz'
     * </code>
     *
     * @param string     $delimiter
     * @param string     $prepend
     * @param array|null $items
     *
     * @return array
     */
    public function flatten($delimiter = '.', $prepend = '', $items = null)
    {
        // init
        $flatten = [];

        if ($items === null) {
            $items = $this->getArray();
        }

        foreach ($items as $key => $value) {
            if (\is_array($value) && $value !== []) {
                $flatten[] = $this->flatten($delimiter, $prepend . $key . $delimiter, $value);
            } else {
                $flatten[] = [$prepend . $key => $value];
            }
        }

        if (\count($flatten) === 0) {
            return [];
        }

        return \array_merge_recursive([], ...$flatten);
    }

    /**
     * WARNING: Creates an Arrayy object by reference.
     *
     * @param array $array
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-param  array<TKey,T> $array
     * @phpstan-return $this<TKey,T>
     *
     * @internal this will not check any types because it's set directly as reference
     */
    public function createByReference(array &$array = []): self
    {
        $this->array = &$array;
        $this->generator = null;

        return $this;
    }

    /**
     * Create an new instance from a callable function which will return an Generator.
     *
     * @param callable $generatorFunction
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     *
     * @phpstan-param callable():\Generator<TKey,T> $generatorFunction
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public static function createFromGeneratorFunction(callable $generatorFunction): self
    {
        return self::create($generatorFunction);
    }

    /**
     * Create an new instance filled with a copy of values from a "Generator"-object.
     *
     * @param \Generator $generator
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     *
     * @phpstan-param \Generator<TKey,T> $generator
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public static function createFromGeneratorImmutable(\Generator $generator): self
    {
        return self::create(\iterator_to_array($generator, true));
    }

    /**
     * Create an new Arrayy object via JSON.
     *
     * @param string $json
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     *
     * @phpstan-return static<int|string,mixed>
     * @psalm-mutation-free
     */
    public static function createFromJson(string $json): self
    {
        return static::create(\json_decode($json, true));
    }

    /**
     * Create an new Arrayy object via JSON.
     *
     * @param array $array
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     *
     * @phpstan-param array<TKey,T> $array
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public static function createFromArray(array $array): self
    {
        return static::create($array);
    }

    /**
     * Create an new instance filled with values from an object that is iterable.
     *
     * @param \Traversable $object <p>iterable object</p>
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     *
     * @phpstan-param \Traversable<array-key,T> $object
     * @phpstan-return static<array-key,T>
     * @psalm-mutation-free
     */
    public static function createFromObject(\Traversable $object): self
    {
        // init
        $arrayy = new static();

        if ($object instanceof self) {
            $objectArray = $object->getGenerator();
        } else {
            $objectArray = $object;
        }

        foreach ($objectArray as $key => $value) {
            /**
             * @psalm-suppress ImpureMethodCall - object is already re-created
             */
            $arrayy->internalSet($key, $value);
        }

        return $arrayy;
    }

    /**
     * Create an new instance filled with values from an object.
     *
     * @param object $object
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     *
     * @phpstan-return static<array-key,mixed>
     * @psalm-mutation-free
     */
    public static function createFromObjectVars($object): self
    {
        return self::create(self::objectToArray($object));
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
     *
     * @phpstan-return static<int,string>
     * @psalm-mutation-free
     */
    public static function createFromString(string $str, string $delimiter = null, string $regEx = null): self
    {
        if ($regEx) {
            \preg_match_all($regEx, $str, $array);

            if (!empty($array)) {
                $array = $array[0];
            }
        } else {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if ($delimiter !== null) {
                $array = \explode($delimiter, $str);
            } else {
                $array = [$str];
            }
        }

        // trim all string in the array
        /**
         * @psalm-suppress MissingClosureParamType
         */
        \array_walk(
            $array,
            static function (&$val) {
                if ((string) $val === $val) {
                    $val = \trim($val);
                }
            }
        );

        return static::create($array);
    }

    /**
     * Create an new instance filled with a copy of values from a "Traversable"-object.
     *
     * @param \Traversable $traversable
     * @param bool         $use_keys    [optional] <p>
     *                                  Whether to use the iterator element keys as index.
     *                                  </p>
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     *
     * @phpstan-param \Traversable<array-key|TKey,T> $traversable
     * @phpstan-return static<int|TKey,T>
     * @psalm-mutation-free
     */
    public static function createFromTraversableImmutable(\Traversable $traversable, bool $use_keys = true): self
    {
        return self::create(\iterator_to_array($traversable, $use_keys));
    }

    /**
     * Create an new instance containing a range of elements.
     *
     * @param float|int|string $low  <p>First value of the sequence.</p>
     * @param float|int|string $high <p>The sequence is ended upon reaching the end value.</p>
     * @param float|int        $step <p>Used as the increment between elements in the sequence.</p>
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the Arrayy object.</p>
     *
     * @phpstan-return static<int,int|string>
     * @psalm-mutation-free
     */
    public static function createWithRange($low, $high, $step = 1): self
    {
        return static::create(\range($low, $high, $step));
    }

    /**
     * Gets the element of the array at the current internal iterator position.
     *
     * @return false|mixed
     *
     * @phpstan-return false|T
     */
    public function current()
    {
        if ($this->generator) {
            return $this->generator->current();
        }

        return \current($this->array);
    }

    /**
     * Custom sort by index via "uksort".
     *
     * EXAMPLE: <code>
     * $callable = function ($a, $b) {
     *     if ($a == $b) {
     *         return 0;
     *     }
     *     return ($a > $b) ? 1 : -1;
     * };
     * $arrayy = a(['three' => 3, 'one' => 1, 'two' => 2]);
     * $resultArrayy = $arrayy->customSortKeys($callable); // Arrayy['one' => 1, 'three' => 3, 'two' => 2]
     * </code>
     *
     * @see          http://php.net/manual/en/function.uksort.php
     *
     * @param callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function customSortKeys(callable $function): self
    {
        $this->generatorToArray();

        \uksort($this->array, $function);

        return $this;
    }

    /**
     * Custom sort by index via "uksort".
     *
     * @see          http://php.net/manual/en/function.uksort.php
     *
     * @param callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function customSortKeysImmutable(callable $function): self
    {
        $that = clone $this;

        $that->generatorToArray();

        /**
         * @psalm-suppress ImpureFunctionCall - object is already cloned
         */
        \uksort($that->array, $function);

        return $that;
    }

    /**
     * Custom sort by value via "usort".
     *
     * EXAMPLE: <code>
     * $callable = function ($a, $b) {
     *     if ($a == $b) {
     *         return 0;
     *     }
     *     return ($a > $b) ? 1 : -1;
     * };
     * $arrayy = a(['three' => 3, 'one' => 1, 'two' => 2]);
     * $resultArrayy = $arrayy->customSortValues($callable); // Arrayy['one' => 1, 'two' => 2, 'three' => 3]
     * </code>
     *
     * @see          http://php.net/manual/en/function.usort.php
     *
     * @param callable $function
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function customSortValues(callable $function): self
    {
        $this->generatorToArray();

        \usort($this->array, $function);

        return $this;
    }

    /**
     * Custom sort by value via "usort".
     *
     * @see          http://php.net/manual/en/function.usort.php
     *
     * @param callable $function
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function customSortValuesImmutable($function): self
    {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        $that->customSortValues($function);

        return $that;
    }

    /**
     * Delete the given key or keys.
     *
     * @param int|int[]|string|string[] $keyOrKeys
     *
     * @return void
     */
    public function delete($keyOrKeys)
    {
        $keyOrKeys = (array) $keyOrKeys;

        foreach ($keyOrKeys as $key) {
            $this->offsetUnset($key);
        }
    }

    /**
     * Return elements where the values that are only in the current array.
     *
     * EXAMPLE: <code>
     * a([1 => 1, 2 => 2])->diff([1 => 1]); // Arrayy[2 => 2]
     * </code>
     *
     * @param array ...$array
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> ...$array
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function diff(array ...$array): self
    {
        if (\count($array) > 1) {
            $array = \array_merge([], ...$array);
        } else {
            $array = $array[0];
        }

        $generator = function () use ($array): \Generator {
            foreach ($this->getGenerator() as $key => $value) {
                if (\in_array($value, $array, true) === false) {
                    yield $key => $value;
                }
            }
        };

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return elements where the keys are only in the current array.
     *
     * @param array ...$array
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> ...$array
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function diffKey(array ...$array): self
    {
        if (\count($array) > 1) {
            $array = \array_replace([], ...$array);
        } else {
            $array = $array[0];
        }

        $generator = function () use ($array): \Generator {
            foreach ($this->getGenerator() as $key => $value) {
                if (\array_key_exists($key, $array) === false) {
                    yield $key => $value;
                }
            }
        };

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return elements where the values and keys are only in the current array.
     *
     * @param array ...$array
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> $array
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function diffKeyAndValue(array ...$array): self
    {
        if (\count($array) > 1) {
            $array = \array_merge([], ...$array);
        } else {
            $array = $array[0];
        }

        $generator = function () use ($array): \Generator {
            foreach ($this->getGenerator() as $key => $value) {
                $isset = isset($array[$key]);

                if (
                    !$isset
                    ||
                    $array[$key] !== $value
                ) {
                    yield $key => $value;
                }
            }
        };

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return elements where the values are only in the current multi-dimensional array.
     *
     * EXAMPLE: <code>
     * a([1 => [1 => 1], 2 => [2 => 2]])->diffRecursive([1 => [1 => 1]]); // Arrayy[2 => [2 => 2]]
     * </code>
     *
     * @param array                 $array
     * @param array|\Generator|null $helperVariableForRecursion <p>(only for internal usage)</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> $array
     * @phpstan-param  null|array<TKey,T>|\Generator<TKey,T> $helperVariableForRecursion
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
                $value = $value->toArray();
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
     * Return elements where the values that are only in the new $array.
     *
     * EXAMPLE: <code>
     * a([1 => 1])->diffReverse([1 => 1, 2 => 2]); // Arrayy[2 => 2]
     * </code>
     *
     * @param array $array
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> $array
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function diffReverse(array $array = []): self
    {
        return static::create(
            \array_diff($array, $this->toArray()),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * EXAMPLE: <code>
     * a(['a' => 1, 'b' => ''])->divide(); // Arrayy[Arrayy['a', 'b'], Arrayy[1, '']]
     * </code>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
     * EXAMPLE: <code>
     * $result = A::create();
     * $closure = function ($value) {
     *     return ':' . $value . ':';
     * };
     * a(['foo', 'bar' => 'bis'])->each($closure); // Arrayy[':foo:', 'bar' => ':bis:']
     * </code>
     *
     * @param \Closure $closure
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param \Closure(T=):T|\Closure(T=,TKey=):T $closure
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
     * Sets the internal iterator to the last element in the array and returns this element.
     *
     * @return false|mixed
     *
     * @phpstan-return T|false
     */
    public function end()
    {
        if ($this->generator) {
            $count = $this->count();
            if ($count === 0) {
                return false;
            }

            $counter = 0;
            foreach ($this->getIterator() as $item) {
                if (++$counter === $count - 1) {
                    break;
                }
            }
        }

        return \end($this->array);
    }

    /**
     * Check if a value is in the current array using a closure.
     *
     * EXAMPLE: <code>
     * $callable = function ($value, $key) {
     *     return 2 === $key and 'two' === $value;
     * };
     * a(['foo', 2 => 'two'])->exists($callable); // true
     * </code>
     *
     * @param \Closure $closure
     *
     * @return bool
     *              <p>Returns true if the given value is found, false otherwise.</p>
     *
     * @phpstan-param \Closure(T=,TKey=):bool $closure
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
     * EXAMPLE: <code>
     * a(['bar'])->fillWithDefaults(3, 'foo'); // Arrayy['bar', 'foo', 'foo']
     * </code>
     *
     * @param int   $num
     * @param mixed $default
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param T $default
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
     * EXAMPLE: <code>
     * $closure = function ($value) {
     *     return $value % 2 !== 0;
     * }
     * a([1, 2, 3, 4])->filter($closure); // Arrayy[0 => 1, 2 => 3]
     * </code>
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
     * @param int           $flag    [optional] <p>
     *                               Flag determining what arguments are sent to <i>callback</i>:
     *                               </p>
     *                               <ul>
     *                               <li>
     *                               <b>ARRAY_FILTER_USE_KEY</b> (1) - pass key as the only argument
     *                               to <i>callback</i> instead of the value
     *                               </li>
     *                               <li>
     *                               <b>ARRAY_FILTER_USE_BOTH</b> (2) - pass both value and key as
     *                               arguments to <i>callback</i> instead of the value
     *                               </li>
     *                               </ul>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param null|(\Closure(T=,TKey=):bool)|(\Closure(T=):bool)|(\Closure(TKey=):bool) $closure
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function filter($closure = null, int $flag = \ARRAY_FILTER_USE_BOTH)
    {
        if (!$closure) {
            return $this->clean();
        }

        if ($flag === \ARRAY_FILTER_USE_KEY) {
            $generator = function () use ($closure) {
                foreach ($this->getGenerator() as $key => $value) {
                    if ($closure($key) === true) {
                        yield $key => $value;
                    }
                }
            };
        } elseif ($flag === \ARRAY_FILTER_USE_BOTH) {
            /** @noinspection PhpSillyAssignmentInspection - hack for phpstan - https://github.com/phpstan/phpstan/issues/4192 */
            /** @phpstan-var \Closure(T=,TKey=):bool $closure */
            $closure = $closure;

            $generator = function () use ($closure) {
                foreach ($this->getGenerator() as $key => $value) {
                    if ($closure($value, $key) === true) {
                        yield $key => $value;
                    }
                }
            };
        } else {
            $generator = function () use ($closure) {
                foreach ($this->getGenerator() as $key => $value) {
                    if ($closure($value) === true) {
                        yield $key => $value;
                    }
                }
            };
        }

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Filters an array of objects (or a numeric array of associative arrays) based on the value of a particular
     * property within that.
     *
     * @param string      $property
     * @param mixed       $value
     * @param string|null $comparisonOp
     *                                  <p>
     *                                  'eq' (equals),<br />
     *                                  'gt' (greater),<br />
     *                                  'gte' || 'ge' (greater or equals),<br />
     *                                  'lt' (less),<br />
     *                                  'lte' || 'le' (less or equals),<br />
     *                                  'ne' (not equals),<br />
     *                                  'contains',<br />
     *                                  'notContains',<br />
     *                                  'newer' (via strtotime),<br />
     *                                  'older' (via strtotime),<br />
     *                                  </p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param mixed|T $value
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     *
     * @psalm-suppress MissingClosureReturnType
     * @psalm-suppress MissingClosureParamType
     */
    public function filterBy(
        string $property,
        $value,
        string $comparisonOp = null
    ): self {
        if (!$comparisonOp) {
            $comparisonOp = \is_array($value) ? 'contains' : 'eq';
        }

        $ops = [
            'eq' => static function ($item, $prop, $value): bool {
                return $item[$prop] === $value;
            },
            'gt' => static function ($item, $prop, $value): bool {
                return $item[$prop] > $value;
            },
            'ge' => static function ($item, $prop, $value): bool {
                return $item[$prop] >= $value;
            },
            'gte' => static function ($item, $prop, $value): bool {
                return $item[$prop] >= $value;
            },
            'lt' => static function ($item, $prop, $value): bool {
                return $item[$prop] < $value;
            },
            'le' => static function ($item, $prop, $value): bool {
                return $item[$prop] <= $value;
            },
            'lte' => static function ($item, $prop, $value): bool {
                return $item[$prop] <= $value;
            },
            'ne' => static function ($item, $prop, $value): bool {
                return $item[$prop] !== $value;
            },
            'contains' => static function ($item, $prop, $value): bool {
                return \in_array($item[$prop], (array) $value, true);
            },
            'notContains' => static function ($item, $prop, $value): bool {
                return !\in_array($item[$prop], (array) $value, true);
            },
            'newer' => static function ($item, $prop, $value): bool {
                return \strtotime($item[$prop]) > \strtotime($value);
            },
            'older' => static function ($item, $prop, $value): bool {
                return \strtotime($item[$prop]) < \strtotime($value);
            },
        ];

        $result = \array_values(
            \array_filter(
                $this->toArray(false, true),
                static function ($item) use (
                    $property,
                    $value,
                    $ops,
                    $comparisonOp
                ) {
                    $item = (array) $item;
                    $itemArrayy = static::create($item);
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
     * Find the first item in an array that passes the truth test, otherwise return false.
     *
     * EXAMPLE: <code>
     * $search = 'foo';
     * $closure = function ($value, $key) use ($search) {
     *     return $value === $search;
     * };
     * a(['foo', 'bar', 'lall'])->find($closure); // 'foo'
     * </code>
     *
     * @param \Closure $closure
     *
     * @return false|mixed
     *                     <p>Return false if we did not find the value.</p>
     *
     * @phpstan-param \Closure(T=,TKey=):bool $closure
     * @phpstan-return T|false
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
     * EXAMPLE: <code>
     * $array = [
     *     0 => ['id' => 123, 'name' => 'foo', 'group' => 'primary', 'value' => 123456, 'when' => '2014-01-01'],
     *     1 => ['id' => 456, 'name' => 'bar', 'group' => 'primary', 'value' => 1468, 'when' => '2014-07-15'],
     * ];
     * a($array)->filterBy('name', 'foo'); // Arrayy[0 => ['id' => 123, 'name' => 'foo', 'group' => 'primary', 'value' => 123456, 'when' => '2014-01-01']]
     * </code>
     *
     * @param string $property
     * @param mixed  $value
     * @param string $comparisonOp
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param mixed|T $value
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function findBy(string $property, $value, string $comparisonOp = 'eq'): self
    {
        return $this->filterBy($property, $value, $comparisonOp);
    }

    /**
     * Get the first value from the current array.
     *
     * EXAMPLE: <code>
     * a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->first(); // 'foo'
     * </code>
     *
     * @return mixed|null
     *                    <p>Return null if there wasn't a element.</p>
     *
     * @phpstan-return T|null
     * @psalm-mutation-free
     */
    public function first()
    {
        $key_first = $this->firstKey();
        if ($key_first === null) {
            return null;
        }

        return $this->get($key_first);
    }

    /**
     * Get the first key from the current array.
     *
     * @return mixed|null
     *                    <p>Return null if there wasn't a element.</p>
     *
     * @psalm-mutation-free
     */
    public function firstKey()
    {
        $this->generatorToArray();

        return \array_key_first($this->array);
    }

    /**
     * Get the first value(s) from the current array.
     * And will return an empty array if there was no first entry.
     *
     * EXAMPLE: <code>
     * a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->firstsImmutable(2); // Arrayy[0 => 'foo', 1 => 'bar']
     * </code>
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function firstsImmutable(int $number = null): self
    {
        $arrayTmp = $this->toArray();

        if ($number === null) {
            $array = (array) \array_shift($arrayTmp);
        } else {
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
     * And will return an empty array if there was no first entry.
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function firstsKeys(int $number = null): self
    {
        $arrayTmp = $this->keys()->toArray();

        if ($number === null) {
            $array = (array) \array_shift($arrayTmp);
        } else {
            $array = \array_splice($arrayTmp, 0, $number);
        }

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get and remove the first value(s) from the current array.
     * And will return an empty array if there was no first entry.
     *
     * EXAMPLE: <code>
     * a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->firstsMutable(); // 'foo'
     * </code>
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return $this
     *               <p>(Mutable)</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function firstsMutable(int $number = null): self
    {
        $this->generatorToArray();

        if ($number === null) {
            $this->array = (array) \array_shift($this->array);
        } else {
            $this->array = \array_splice($this->array, 0, $number);
        }

        return $this;
    }

    /**
     * Exchanges all keys with their associated values in an array.
     *
     * EXAMPLE: <code>
     * a([0 => 'foo', 1 => 'bar'])->flip(); // Arrayy['foo' => 0, 'bar' => 1]
     * </code>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<array-key,TKey>
     * @psalm-mutation-free
     */
    public function flip(): self
    {
        $generator = function (): \Generator {
            foreach ($this->getGenerator() as $key => $value) {
                yield (string) $value => $key;
            }
        };

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get a value from an array (optional using dot-notation).
     *
     * EXAMPLE: <code>
     * $arrayy = a(['user' => ['lastname' => 'Moelleken']]);
     * $arrayy->get('user.lastname'); // 'Moelleken'
     * // ---
     * $arrayy = new A();
     * $arrayy['user'] = ['lastname' => 'Moelleken'];
     * $arrayy['user.firstname'] = 'Lars';
     * $arrayy['user']['lastname']; // Moelleken
     * $arrayy['user.lastname']; // Moelleken
     * $arrayy['user.firstname']; // Lars
     * </code>
     *
     * @param int|string $key            <p>The key to look for.</p>
     * @param mixed      $fallback       <p>Value to fallback to.</p>
     * @param array|null $array          <p>The array to get from, if it's set to "null" we use the current array from the
     *                                   class.</p>
     * @param bool       $useByReference
     *
     * @return mixed|static
     *
     * @phpstan-param array-key $key
     * @phpstan-param array<array-key,mixed>|array<TKey,T> $array
     * @psalm-mutation-free
     */
    public function get(
        $key = null,
        $fallback = null,
        array $array = null,
        bool $useByReference = false
    ) {
        if ($array === null && $key === null) {
            if ($useByReference) {
                return $this;
            }

            return clone $this;
        }

        if ($array !== null) {
            if ($useByReference) {
                $usedArray = &$array;
            } else {
                $usedArray = $array;
            }
        } else {
            $this->generatorToArray();

            if ($useByReference) {
                $usedArray = &$this->array;
            } else {
                $usedArray = $this->array;
            }
        }

        if ($key === null) {
            return static::create(
                [],
                $this->iteratorClass,
                false
            )->createByReference($usedArray);
        }

        // php cast "bool"-index into "int"-index
        /** @phpstan-ignore-next-line | this is only a fallback */
        if ((bool) $key === $key) {
            $key = (int) $key;
        }

        if (\array_key_exists($key, $usedArray) === true) {
            if (\is_array($usedArray[$key])) {
                return static::create(
                    [],
                    $this->iteratorClass,
                    false
                )->createByReference($usedArray[$key]);
            }

            return $usedArray[$key];
        }

        // crawl through array, get key according to object or not
        $usePath = false;
        if (
            $this->pathSeparator
            &&
            (string) $key === $key
            &&
            \strpos($key, $this->pathSeparator) !== false
        ) {
            $segments = \explode($this->pathSeparator, (string) $key);
            if ($segments !== false) {
                $usePath = true;
                $usedArrayTmp = $usedArray; // do not use the reference for dot-annotations

                foreach ($segments as $segment) {
                    if (
                        (
                            \is_array($usedArrayTmp)
                            ||
                            $usedArrayTmp instanceof \ArrayAccess
                        )
                        &&
                        isset($usedArrayTmp[$segment])
                    ) {
                        $usedArrayTmp = $usedArrayTmp[$segment];

                        continue;
                    }

                    if (
                        \is_object($usedArrayTmp) === true
                        &&
                        \property_exists($usedArrayTmp, $segment)
                    ) {
                        $usedArrayTmp = $usedArrayTmp->{$segment};

                        continue;
                    }

                    if (isset($segments[0]) && $segments[0] === '*') {
                        $segmentsTmp = $segments;
                        unset($segmentsTmp[0]);
                        $keyTmp = \implode('.', $segmentsTmp);
                        $returnTmp = static::create(
                            [],
                            $this->iteratorClass,
                            false
                        );
                        foreach ($this->getAll() as $dataTmp) {
                            if ($dataTmp instanceof self) {
                                $returnTmp->add($dataTmp->get($keyTmp));

                                continue;
                            }

                            if (
                                (
                                    \is_array($dataTmp)
                                    ||
                                    $dataTmp instanceof \ArrayAccess
                                )
                                &&
                                isset($dataTmp[$keyTmp])
                            ) {
                                $returnTmp->add($dataTmp[$keyTmp]);

                                continue;
                            }

                            if (
                                \is_object($dataTmp) === true
                                &&
                                \property_exists($dataTmp, $keyTmp)
                            ) {
                                $returnTmp->add($dataTmp->{$keyTmp});

                                /** @noinspection UnnecessaryContinueInspection */
                                continue;
                            }
                        }

                        if ($returnTmp->count() > 0) {
                            return $returnTmp;
                        }
                    }

                    return $fallback instanceof \Closure ? $fallback() : $fallback;
                }
            }
        }

        if (isset($usedArrayTmp)) {
            if (!$usePath && !isset($usedArrayTmp[$key])) {
                return $fallback instanceof \Closure ? $fallback() : $fallback;
            }

            if (\is_array($usedArrayTmp)) {
                return static::create(
                    [],
                    $this->iteratorClass,
                    false
                )->createByReference($usedArrayTmp);
            }

            return $usedArrayTmp;
        }

        if (!$usePath && !isset($usedArray[$key])) {
            return $fallback instanceof \Closure ? $fallback() : $fallback;
        }

        return static::create(
            [],
            $this->iteratorClass,
            false
        )->createByReference($usedArray);
    }

    /**
     * alias: for "Arrayy->toArray()"
     *
     * @return array
     *
     * @see          Arrayy::getArray()
     *
     * @phpstan-return array<TKey,T>
     */
    public function getAll(): array
    {
        return $this->toArray();
    }

    /**
     * Get the current array from the "Arrayy"-object.
     *
     * alias for "toArray()"
     *
     * @param bool $convertAllArrayyElements <p>
     *                                       Convert all Child-"Arrayy" objects also to arrays.
     *                                       </p>
     * @param bool $preserveKeys             <p>
     *                                       e.g.: A generator maybe return the same key more then once,
     *                                       so maybe you will ignore the keys.
     *                                       </p>
     *
     * @return array
     *
     * @phpstan-return array<TKey,T>
     * @psalm-mutation-free
     *
     * @see Arrayy::toArray()
     */
    public function getArray(
        bool $convertAllArrayyElements = false,
        bool $preserveKeys = true
    ): array {
        return $this->toArray(
            $convertAllArrayyElements,
            $preserveKeys
        );
    }

    /**
     * @param string $json
     *
     * @return $this
     */
    public static function createFromJsonMapper(string $json)
    {
        // init
        $class = static::create();

        $jsonObject = \json_decode($json, false);

        $mapper = new \Arrayy\Mapper\Json();
        $mapper->undefinedPropertyHandler = static function ($object, $key, $jsonValue) use ($class) {
            if ($class->checkPropertiesMismatchInConstructor) {
                throw new \TypeError('Property mismatch - input: ' . \print_r(['key' => $key, 'jsonValue' => $jsonValue], true) . ' for object: ' . \get_class($object));
            }
        };

        return $mapper->map($jsonObject, $class);
    }

    /**
     * @return array<int|string,TypeCheckInterface>|mixed|TypeCheckArray<int|string,TypeCheckInterface>|TypeInterface
     *
     * @internal
     */
    public function getPhpDocPropertiesFromClass()
    {
        if ($this->properties === []) {
            $this->properties = $this->getPropertiesFromPhpDoc();
        }

        return $this->properties;
    }

    /**
     * Get the current array from the "Arrayy"-object as list.
     *
     * alias for "toList()"
     *
     * @param bool $convertAllArrayyElements <p>
     *                                       Convert all Child-"Arrayy" objects also to arrays.
     *                                       </p>
     *
     * @return array
     *
     * @phpstan-return list<mixed>|list<T>
     * @psalm-mutation-free
     *
     * @see Arrayy::toList()
     */
    public function getList(bool $convertAllArrayyElements = false): array
    {
        return $this->toList($convertAllArrayyElements);
    }

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey, can be used to extract data-columns from multi-arrays.
     *
     * EXAMPLE: <code>
     * a([['foo' => 'bar', 'id' => 1], ['foo => 'lall', 'id' => 2]])->getColumn('foo', 'id'); // Arrayy[1 => 'bar', 2 => 'lall']
     * </code>
     *
     * INFO: Optionally, you may provide an $indexKey to index the values in the returned
     *       array by the values from the $indexKey column in the input array.
     *
     * @param int|string|null $columnKey
     * @param int|string|null $indexKey
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function getColumn($columnKey = null, $indexKey = null): self
    {
        if ($columnKey === null && $indexKey === null) {
            $generator = function () {
                foreach ($this->getGenerator() as $key => $value) {
                    yield $value;
                }
            };
        } else {
            $generator = function () use ($columnKey, $indexKey) {
                foreach ($this->getGenerator() as $key => $value) {
                    // reset
                    $newKey = null;
                    $newValue = null;
                    $newValueFound = false;

                    if ($indexKey !== null) {
                        foreach ($value as $keyInner => $valueInner) {
                            if ($indexKey === $keyInner) {
                                $newKey = $valueInner;
                            }

                            if ($columnKey === $keyInner) {
                                $newValue = $valueInner;
                                $newValueFound = true;
                            }
                        }
                    } else {
                        foreach ($value as $keyInner => $valueInner) {
                            if ($columnKey === $keyInner) {
                                $newValue = $valueInner;
                                $newValueFound = true;
                            }
                        }
                    }

                    if ($newValueFound === false) {
                        if ($newKey !== null) {
                            yield $newKey => $value;
                        } else {
                            yield $value;
                        }
                    } else {
                        /** @noinspection NestedPositiveIfStatementsInspection */
                        if ($newKey !== null) {
                            yield $newKey => $newValue;
                        } else {
                            yield $newValue;
                        }
                    }
                }
            };
        }

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get the current array from the "Arrayy"-object as generator by reference.
     *
     * @return \Generator
     *
     * @phpstan-return \Generator<mixed,T>|\Generator<TKey,T>
     */
    public function &getGeneratorByReference(): \Generator
    {
        if ($this->generator instanceof ArrayyRewindableGenerator) {
            // -> false-positive -> see "&" from method
            /** @noinspection YieldFromCanBeUsedInspection */
            foreach ($this->generator as $key => $value) {
                yield $key => $value;
            }

            return;
        }

        // -> false-positive -> see "&$value"
        /** @noinspection YieldFromCanBeUsedInspection */
        /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
        foreach ($this->array as $key => &$value) {
            yield $key => $value;
        }
    }

    /**
     * Get the current array from the "Arrayy"-object as generator.
     *
     * @return \Generator
     *
     * @phpstan-return \Generator<mixed,T>|\Generator<TKey,T>
     * @psalm-mutation-free
     */
    public function getGenerator(): \Generator
    {
        if ($this->generator instanceof ArrayyRewindableGenerator) {
            yield from $this->generator;

            return;
        }

        yield from $this->array;
    }

    /**
     * alias: for "Arrayy->keys()"
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @see          Arrayy::keys()
     *
     * @phpstan-return static<int,TKey>
     * @psalm-mutation-free
     */
    public function getKeys()
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
        return self::arrayToObject($this->toArray());
    }

    /**
     * alias: for "Arrayy->randomImmutable()"
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @see          Arrayy::randomImmutable()
     *
     * @phpstan-return static<int|array-key,T>
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
     * @see          Arrayy::randomKeys()
     *
     * @phpstan-return static<TKey,T>
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
     * @see          Arrayy::randomValues()
     *
     * @phpstan-return static<TKey,T>
     */
    public function getRandomValues(int $number): self
    {
        return $this->randomValues($number);
    }

    /**
     * Gets all values.
     *
     * @return static
     *                <p>The values of all elements in this array, in the order they
     *                appear in the array.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function getValues()
    {
        $this->generatorToArray(false);

        return static::create(
            \array_values($this->array),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Gets all values via Generator.
     *
     * @return \Generator
     *                    <p>The values of all elements in this array, in the order they
     *                    appear in the array as Generator.</p>
     *
     * @phpstan-return \Generator<TKey,T>
     */
    public function getValuesYield(): \Generator
    {
        yield from $this->getGenerator();
    }

    /**
     * Group values from a array according to the results of a closure.
     *
     * @param callable|string $grouper  <p>A callable function name.</p>
     * @param bool            $saveKeys
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function group($grouper, bool $saveKeys = false): self
    {
        // init
        $result = [];

        // Iterate over values, group by property/results from closure.
        foreach ($this->getGenerator() as $key => $value) {
            if (\is_callable($grouper) === true) {
                $groupKey = $grouper($value, $key);
            } else {
                $groupKey = $this->get($grouper);
            }

            $newValue = $this->get($groupKey, null, $result);

            if ($groupKey instanceof self) {
                $groupKey = $groupKey->toArray();
            }

            if ($newValue instanceof self) {
                $newValue = $newValue->toArray();
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
            $UN_FOUND = 'arrayy--' . \uniqid('arrayy', true);
        }

        if (\is_array($key)) {
            if ($key === []) {
                return false;
            }

            foreach ($key as $keyTmp) {
                $found = ($this->get($keyTmp, $UN_FOUND) !== $UN_FOUND);
                if ($found === false) {
                    return false;
                }
            }

            return true;
        }

        return $this->get($key, $UN_FOUND) !== $UN_FOUND;
    }

    /**
     * Check if an array has a given value.
     *
     * INFO: If you need to search recursive please use ```contains($value, true)```.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function hasValue($value): bool
    {
        return $this->contains($value);
    }

    /**
     * Implodes the values of this array.
     *
     * EXAMPLE: <code>
     * a([0 => -9, 1, 2])->implode('|'); // '-9|1|2'
     * </code>
     *
     * @param string $glue
     * @param string $prefix
     *
     * @return string
     * @psalm-mutation-free
     */
    public function implode(string $glue = '', string $prefix = ''): string
    {
        return $prefix . $this->implode_recursive($glue, $this->toArray(), false);
    }

    /**
     * Implodes the keys of this array.
     *
     * @param string $glue
     *
     * @return string
     * @psalm-mutation-free
     */
    public function implodeKeys(string $glue = ''): string
    {
        return $this->implode_recursive($glue, $this->toArray(), true);
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
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
     * @return false|mixed
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
     * EXAMPLE: <code>
     * a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->initial(2); // Arrayy[0 => 'foo']
     * </code>
     *
     * @param int $to
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function initial(int $to = 1): self
    {
        return $this->firstsImmutable(\count($this->toArray(), \COUNT_NORMAL) - $to);
    }

    /**
     * Return an array with all elements found in input array.
     *
     * EXAMPLE: <code>
     * a(['foo', 'bar'])->intersection(['bar', 'baz']); // Arrayy['bar']
     * </code>
     *
     * @param array $search
     * @param bool  $keepKeys
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> $search
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function intersection(array $search, bool $keepKeys = false): self
    {
        if ($keepKeys) {
            /**
             * @psalm-suppress MissingClosureReturnType
             * @psalm-suppress MissingClosureParamType
             */
            return static::create(
                \array_uintersect(
                    $this->toArray(),
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
            \array_values(\array_intersect($this->toArray(), $search)),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return an array with all elements found in input array.
     *
     * @param array ...$array
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<array<TKey,T>> ...$array
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function intersectionMulti(...$array): self
    {
        return static::create(
            \array_values(\array_intersect($this->toArray(), ...$array)),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return a boolean flag which indicates whether the two input arrays have any common elements.
     *
     * EXAMPLE: <code>
     * a(['foo', 'bar'])->intersects(['föö', 'bär']); // false
     * </code>
     *
     * @param array $search
     *
     * @return bool
     *
     * @phpstan-param array<TKey,T> $search
     */
    public function intersects(array $search): bool
    {
        return $this->intersection($search)->count() > 0;
    }

    /**
     * Invoke a function on all of an array's values.
     *
     * @param callable $callable
     * @param mixed    $arguments
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  callable(T=,mixed):mixed $callable
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function invoke($callable, $arguments = []): self
    {
        // If one argument given for each iteration, create an array for it.
        if (!\is_array($arguments)) {
            $arguments = \array_fill(
                0,
                $this->count(),
                $arguments
            );
        }

        // If the callable has arguments, pass them.
        if ($arguments) {
            $array = \array_map($callable, $this->toArray(), $arguments);
        } else {
            $array = $this->map($callable);
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
     * EXAMPLE: <code>
     * a(['foo' => 'bar', 2, 3])->isAssoc(); // true
     * </code>
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

        /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
        foreach ($this->keys($recursive)->getGeneratorByReference() as &$key) {
            if ((string) $key !== $key) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a given key or keys are empty.
     *
     * @param int|int[]|string|string[]|null $keys
     *
     * @return bool
     *              <p>Returns true if empty, false otherwise.</p>
     * @psalm-mutation-free
     */
    public function isEmpty($keys = null): bool
    {
        if ($this->generator) {
            return $this->toArray() === [];
        }

        if ($keys === null) {
            return $this->array === [];
        }

        foreach ((array) $keys as $key) {
            if (!empty($this->get($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the current array is equal to the given "$array" or not.
     *
     * EXAMPLE: <code>
     * a(['💩'])->isEqual(['💩']); // true
     * </code>
     *
     * @param array $array
     *
     * @return bool
     *
     * @phpstan-param array<int|string,mixed> $array
     */
    public function isEqual(array $array): bool
    {
        return $this->toArray() === $array;
    }

    /**
     * Check if the current array is a multi-array.
     *
     * EXAMPLE: <code>
     * a(['foo' => [1, 2 , 3]])->isMultiArray(); // true
     * </code>
     *
     * @return bool
     */
    public function isMultiArray(): bool
    {
        foreach ($this->getGenerator() as $key => $value) {
            if (\is_array($value)) {
                return true;
            }
        }

        return false;
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

        /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
        foreach ($this->keys()->getGeneratorByReference() as &$key) {
            if ((int) $key !== $key) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the current array is sequential [0, 1, 2, 3, 4, 5 ...] or not.
     *
     * EXAMPLE: <code>
     * a([0 => 'foo', 1 => 'lall', 2 => 'foobar'])->isSequential(); // true
     * </code>
     *
     * INFO: If the array is empty we count it as non-sequential.
     *
     * @param bool $recursive
     *
     * @return bool
     * @psalm-mutation-free
     */
    public function isSequential(bool $recursive = false): bool
    {
        $i = 0;
        foreach ($this->getGenerator() as $key => $value) {
            /** @noinspection IsIterableCanBeUsedInspection */
            if (
                $recursive
                &&
                (\is_array($value) || $value instanceof \Traversable)
                &&
                self::create($value)->isSequential() === false
            ) {
                return false;
            }

            if ($key !== $i) {
                return false;
            }

            ++$i;
        }

        /** @noinspection IfReturnReturnSimplificationInspection */
        if ($i === 0) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     *
     * @phpstan-return array<TKey,T>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Gets the key/index of the element at the current internal iterator position.
     *
     * @return int|string|null
     * @phpstan-return array-key|null
     */
    public function key()
    {
        if ($this->generator) {
            return $this->generator->key();
        }

        return \key($this->array);
    }

    /**
     * Checks if the given key exists in the provided array.
     *
     * INFO: This method only use "array_key_exists()" if you want to use "dot"-notation,
     *       then you need to use "Arrayy->offsetExists()".
     *
     * @param int|string $key the key to look for
     *
     * @return bool
     * @psalm-mutation-free
     */
    public function keyExists($key): bool
    {
        foreach ($this->getGenerator() as $keyTmp => $value) {
            if ($key === $keyTmp) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all keys from the current array.
     *
     * EXAMPLE: <code>
     * a([1 => 'foo', 2 => 'foo2', 3 => 'bar'])->keys(); // Arrayy[1, 2, 3]
     * </code>
     *
     * @param bool       $recursive     [optional] <p>
     *                                  Get all keys, also from all sub-arrays from an multi-dimensional array.
     *                                  </p>
     * @param mixed|null $search_values [optional] <p>
     *                                  If specified, then only keys containing these values are returned.
     *                                  </p>
     * @param bool       $strict        [optional] <p>
     *                                  Determines if strict comparison (===) should be used during the search.
     *                                  </p>
     *
     * @return static
     *                <p>(Immutable) An array of all the keys in input.</p>
     *
     * @phpstan-return static<int,TKey>
     * @psalm-mutation-free
     */
    public function keys(
        bool $recursive = false,
        $search_values = null,
        bool $strict = true
    ): self {

        // recursive

        if ($recursive === true) {
            $array = $this->array_keys_recursive(
                null,
                $search_values,
                $strict
            );

            return static::create(
                $array,
                $this->iteratorClass,
                false
            );
        }

        // non recursive

        if ($search_values === null) {
            $arrayFunction = function (): \Generator {
                foreach ($this->getGenerator() as $key => $value) {
                    yield $key;
                }
            };
        } else {
            $arrayFunction = function () use ($search_values, $strict): \Generator {
                $is_array_tmp = \is_array($search_values);

                /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
                foreach ($this->getGeneratorByReference() as $key => &$value) {
                    if (
                        (
                            $is_array_tmp === false
                            &&
                            $strict === true
                            &&
                            $search_values === $value
                        )
                        ||
                        (
                            $is_array_tmp === false
                            &&
                            $strict === false
                            &&
                            $search_values == $value
                        )
                        ||
                        (
                            $is_array_tmp === true
                            &&
                            \in_array($value, $search_values, $strict)
                        )
                    ) {
                        yield $key;
                    }
                }
            };
        }

        return static::create(
            $arrayFunction,
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
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function krsort(int $sort_flags = 0): self
    {
        $this->generatorToArray();

        \krsort($this->array, $sort_flags);

        return $this;
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
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function krsortImmutable(int $sort_flags = 0): self
    {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        $that->krsort($sort_flags);

        return $that;
    }

    /**
     * Get the last value from the current array.
     *
     * EXAMPLE: <code>
     * a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->last(); // 'lall'
     * </code>
     *
     * @return mixed|null
     *                    <p>Return null if there wasn't a element.</p>
     *
     * @phpstan-return T|null
     * @psalm-mutation-free
     */
    public function last()
    {
        $key_last = $this->lastKey();
        if ($key_last === null) {
            return null;
        }

        return $this->get($key_last);
    }

    /**
     * Get the last key from the current array.
     *
     * @return mixed|null
     *                    <p>Return null if there wasn't a element.</p>
     * @psalm-mutation-free
     */
    public function lastKey()
    {
        $this->generatorToArray();

        return \array_key_last($this->array);
    }

    /**
     * Get the last value(s) from the current array.
     *
     * EXAMPLE: <code>
     * a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->lasts(2); // Arrayy[0 => 'bar', 1 => 'lall']
     * </code>
     *
     * @param int|null $number
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
            $poppedValue = $this->last();

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
            $arrayy = $this->rest(-$number);
        }

        return $arrayy;
    }

    /**
     * Get the last value(s) from the current array.
     *
     * EXAMPLE: <code>
     * a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->lasts(2); // Arrayy[0 => 'bar', 1 => 'lall']
     * </code>
     *
     * @param int|null $number
     *
     * @return $this
     *               <p>(Mutable)</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function lastsMutable(int $number = null): self
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $this->array = $this->lastsImmutable($number)->toArray();
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
     * EXAMPLE: <code>
     * a(['foo', 'Foo'])->map('mb_strtoupper'); // Arrayy['FOO', 'FOO']
     * </code>
     *
     * @param callable $callable
     * @param bool     $useKeyAsSecondParameter
     * @param mixed    ...$arguments
     *
     * @return static
     *                <p>(Immutable) Arrayy object with modified elements.</p>
     *
     * @template T2
     *              <p>The output value type.</p>
     *
     * @phpstan-param callable(T,TKey=,mixed=):T2 $callable
     * @phpstan-return static<TKey,T2>
     * @psalm-mutation-free
     */
    public function map(
        callable $callable,
        bool $useKeyAsSecondParameter = false,
        ...$arguments
    ) {
        /**
         * @psalm-suppress ImpureFunctionCall - func_num_args is only used to detect the number of args
         */
        $useArguments = \func_num_args() > 2;

        return static::create(
            function () use ($useArguments, $callable, $useKeyAsSecondParameter, $arguments) {
                foreach ($this->getGenerator() as $key => $value) {
                    if ($useArguments) {
                        if ($useKeyAsSecondParameter) {
                            yield $key => $callable($value, $key, ...$arguments);
                        } else {
                            yield $key => $callable($value, ...$arguments);
                        }
                    } else {
                        /** @noinspection NestedPositiveIfStatementsInspection */
                        if ($useKeyAsSecondParameter) {
                            yield $key => $callable($value, $key);
                        } else {
                            yield $key => $callable($value);
                        }
                    }
                }
            },
            $this->iteratorClass,
            false
        );
    }

    /**
     * Check if all items in current array match a truth test.
     *
     * EXAMPLE: <code>
     * $closure = function ($value, $key) {
     *     return ($value % 2 === 0);
     * };
     * a([2, 4, 8])->matches($closure); // true
     * </code>
     *
     * @param \Closure $closure
     *
     * @return bool
     *
     * @phpstan-param \Closure(T=,TKey=):bool $closure
     */
    public function matches(\Closure $closure): bool
    {
        if ($this->count() === 0) {
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
     * EXAMPLE: <code>
     * $closure = function ($value, $key) {
     *     return ($value % 2 === 0);
     * };
     * a([1, 4, 7])->matches($closure); // true
     * </code>
     *
     * @param \Closure $closure
     *
     * @return bool
     *
     * @phpstan-param \Closure(T=,TKey=):bool $closure
     */
    public function matchesAny(\Closure $closure): bool
    {
        if ($this->count() === 0) {
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
     * EXAMPLE: <code>
     * a([-9, -8, -7, 1.32])->max(); // 1.32
     * </code>
     *
     * @return false|float|int|string
     *                                <p>Will return false if there are no values.</p>
     */
    public function max()
    {
        if ($this->count() === 0) {
            return false;
        }

        $max = false;
        /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
        foreach ($this->getGeneratorByReference() as &$value) {
            if (
                $max === false
                ||
                $value > $max
            ) {
                $max = $value;
            }
        }

        return $max;
    }

    /**
     * Merge the new $array into the current array.
     *
     * - keep key,value from the current array, also if the index is in the new $array
     *
     * EXAMPLE: <code>
     * $array1 = [1 => 'one', 'foo' => 'bar1'];
     * $array2 = ['foo' => 'bar2', 3 => 'three'];
     * a($array1)->mergeAppendKeepIndex($array2); // Arrayy[1 => 'one', 'foo' => 'bar2', 3 => 'three']
     * // ---
     * $array1 = [0 => 'one', 1 => 'foo'];
     * $array2 = [0 => 'foo', 1 => 'bar2'];
     * a($array1)->mergeAppendKeepIndex($array2); // Arrayy[0 => 'foo', 1 => 'bar2']
     * </code>
     *
     * @param array $array
     * @param bool  $recursive
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<int|TKey,T> $array
     * @phpstan-return static<int|TKey,T>
     * @psalm-mutation-free
     */
    public function mergeAppendKeepIndex(array $array = [], bool $recursive = false): self
    {
        if ($recursive === true) {
            $array = $this->getArrayRecursiveHelperArrayy($array);
            $result = \array_replace_recursive($this->toArray(), $array);
        } else {
            $result = \array_replace($this->toArray(), $array);
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
     * EXAMPLE: <code>
     * $array1 = [1 => 'one', 'foo' => 'bar1'];
     * $array2 = ['foo' => 'bar2', 3 => 'three'];
     * a($array1)->mergeAppendNewIndex($array2); // Arrayy[0 => 'one', 'foo' => 'bar2', 1 => 'three']
     * // ---
     * $array1 = [0 => 'one', 1 => 'foo'];
     * $array2 = [0 => 'foo', 1 => 'bar2'];
     * a($array1)->mergeAppendNewIndex($array2); // Arrayy[0 => 'one', 1 => 'foo', 2 => 'foo', 3 => 'bar2']
     * </code>
     *
     * @param array $array
     * @param bool  $recursive
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> $array
     * @phpstan-return static<int,T>
     * @psalm-mutation-free
     */
    public function mergeAppendNewIndex(array $array = [], bool $recursive = false): self
    {
        if ($recursive === true) {
            $array = $this->getArrayRecursiveHelperArrayy($array);
            $result = \array_merge_recursive($this->toArray(), $array);
        } else {
            $result = \array_merge($this->toArray(), $array);
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
     * EXAMPLE: <code>
     * $array1 = [1 => 'one', 'foo' => 'bar1'];
     * $array2 = ['foo' => 'bar2', 3 => 'three'];
     * a($array1)->mergePrependKeepIndex($array2); // Arrayy['foo' => 'bar1', 3 => 'three', 1 => 'one']
     * // ---
     * $array1 = [0 => 'one', 1 => 'foo'];
     * $array2 = [0 => 'foo', 1 => 'bar2'];
     * a($array1)->mergePrependKeepIndex($array2); // Arrayy[0 => 'one', 1 => 'foo']
     * </code>
     *
     * @param array $array
     * @param bool  $recursive
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> $array
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function mergePrependKeepIndex(array $array = [], bool $recursive = false): self
    {
        if ($recursive === true) {
            $array = $this->getArrayRecursiveHelperArrayy($array);
            $result = \array_replace_recursive($array, $this->toArray());
        } else {
            $result = \array_replace($array, $this->toArray());
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
     * EXAMPLE: <code>
     * $array1 = [1 => 'one', 'foo' => 'bar1'];
     * $array2 = ['foo' => 'bar2', 3 => 'three'];
     * a($array1)->mergePrependNewIndex($array2); // Arrayy['foo' => 'bar1', 0 => 'three', 1 => 'one']
     * // ---
     * $array1 = [0 => 'one', 1 => 'foo'];
     * $array2 = [0 => 'foo', 1 => 'bar2'];
     * a($array1)->mergePrependNewIndex($array2); // Arrayy[0 => 'foo', 1 => 'bar2', 2 => 'one', 3 => 'foo']
     * </code>
     *
     * @param array $array
     * @param bool  $recursive
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> $array
     * @phpstan-return static<int,T>
     * @psalm-mutation-free
     */
    public function mergePrependNewIndex(array $array = [], bool $recursive = false): self
    {
        if ($recursive === true) {
            $array = $this->getArrayRecursiveHelperArrayy($array);
            $result = \array_merge_recursive($array, $this->toArray());
        } else {
            $result = \array_merge($array, $this->toArray());
        }

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * @return ArrayyMeta|mixed|static
     */
    public static function meta()
    {
        return (new ArrayyMeta())->getMetaObject(static::class);
    }

    /**
     * Get the min value from an array.
     *
     * EXAMPLE: <code>
     * a([-9, -8, -7, 1.32])->min(); // -9
     * </code>
     *
     * @return false|mixed
     *                     <p>Will return false if there are no values.</p>
     */
    public function min()
    {
        if ($this->count() === 0) {
            return false;
        }

        $min = false;
        /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
        foreach ($this->getGeneratorByReference() as &$value) {
            if (
                $min === false
                ||
                $value < $min
            ) {
                $min = $value;
            }
        }

        return $min;
    }

    /**
     * Get the most used value from the array.
     *
     * @return mixed|null
     *                    <p>(Immutable) Return null if there wasn't a element.</p>
     *
     * @phpstan-return T|null
     * @psalm-mutation-free
     */
    public function mostUsedValue()
    {
        return $this->countValues()->arsortImmutable()->firstKey();
    }

    /**
     * Get the most used value from the array.
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function mostUsedValues(int $number = null): self
    {
        return $this->countValues()->arsortImmutable()->firstsKeys($number);
    }

    /**
     * Move an array element to a new index.
     *
     * EXAMPLE: <code>
     * $arr2 = new A(['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e']);
     * $newArr2 = $arr2->moveElement('D', 1); // Arrayy['A' => 'a', 'D' => 'd', 'B' => 'b', 'C' => 'c', 'E' => 'e']
     * </code>
     *
     * @param int|string $from
     * @param int        $to
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function moveElement($from, $to): self
    {
        $array = $this->toArray();

        if ((int) $from === $from) {
            $tmp = \array_splice($array, $from, 1);
            \array_splice($array, (int) $to, 0, $tmp);
            $output = $array;
        } elseif ((string) $from === $from) {
            $indexToMove = \array_search($from, \array_keys($array), true);
            $itemToMove = $array[$from];
            if ($indexToMove !== false) {
                \array_splice($array, $indexToMove, 1);
            }
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
     * Move an array element to the first place.
     *
     * INFO: Instead of "Arrayy->moveElement()" this method will NOT
     *       loss the keys of an indexed array.
     *
     * @param int|string $key
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function moveElementToFirstPlace($key): self
    {
        $array = $this->toArray();

        if ($this->offsetExists($key)) {
            $tmpValue = $this->get($key);
            unset($array[$key]);
            $array = [$key => $tmpValue] + $array;
        }

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Move an array element to the last place.
     *
     * INFO: Instead of "Arrayy->moveElement()" this method will NOT
     *       loss the keys of an indexed array.
     *
     * @param int|string $key
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function moveElementToLastPlace($key): self
    {
        $array = $this->toArray();

        if ($this->offsetExists($key)) {
            $tmpValue = $this->get($key);
            unset($array[$key]);
            $array += [$key => $tmpValue];
        }

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return false|mixed
     *                     <p>(Mutable) Will return false if there are no values.</p>
     *
     * @phpstan-return false|T
     */
    public function next()
    {
        if ($this->generator) {
            $this->generator->next();

            return $this->generator->current() ?? false;
        }

        return \next($this->array);
    }

    /**
     * Get the next nth keys and values from the array.
     *
     * @param int $step
     * @param int $offset
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function nth(int $step, int $offset = 0): self
    {
        $arrayFunction = function () use ($step, $offset): \Generator {
            $position = 0;
            foreach ($this->getGenerator() as $key => $value) {
                if ($position++ % $step !== $offset) {
                    continue;
                }

                yield $key => $value;
            }
        };

        return static::create(
            $arrayFunction,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param int[]|string[] $keys
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param array-key[] $keys
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function only(array $keys): self
    {
        $keys = \array_flip($keys);

        $generator = function () use ($keys): \Generator {
            foreach ($this->getGenerator() as $key => $value) {
                if (isset($keys[$key])) {
                    yield $key => $value;
                }
            }
        };

        return static::create(
            $generator,
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
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function pad(int $size, $value): self
    {
        return static::create(
            \array_pad($this->toArray(), $size, $value),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Partitions this array in two array according to a predicate.
     * Keys are preserved in the resulting array.
     *
     * @param \Closure $closure
     *                          <p>The predicate on which to partition.</p>
     *
     * @return array<int, static>
     *                    <p>An array with two elements. The first element contains the array
     *                    of elements where the predicate returned TRUE, the second element
     *                    contains the array of elements where the predicate returned FALSE.</p>
     *
     * @phpstan-param \Closure(T=,TKey=):bool $closure
     * @phpstan-return array<int, static<TKey,T>>
     */
    public function partition(\Closure $closure): array
    {
        // init
        $matches = [];
        $noMatches = [];

        foreach ($this->getGenerator() as $key => $value) {
            if ($closure($value, $key)) {
                $matches[$key] = $value;
            } else {
                $noMatches[$key] = $value;
            }
        }

        return [self::create($matches), self::create($noMatches)];
    }

    /**
     * Pop a specified value off the end of the current array.
     *
     * @return mixed|null
     *                    <p>(Mutable) The popped element from the current array or null if the array is e.g. empty.</p>
     *
     * @phpstan-return T|null
     */
    public function pop()
    {
        $this->generatorToArray();

        return \array_pop($this->array);
    }

    /**
     * Prepend a (key) + value to the current array.
     *
     * EXAMPLE: <code>
     * a(['fòô' => 'bàř'])->prepend('foo'); // Arrayy[0 => 'foo', 'fòô' => 'bàř']
     * </code>
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object, with the prepended value.</p>
     *
     * @phpstan-param T $value
     * @phpstan-param TKey|null $key
     * @phpstan-return static<TKey,T>
     */
    public function prepend($value, $key = null)
    {
        $this->generatorToArray();

        if ($this->properties !== []) {
            $this->checkType($key, $value);
        }

        if ($key === null) {
            \array_unshift($this->array, $value);
        } else {
            $this->array = [$key => $value] + $this->array;
        }

        return $this;
    }

    /**
     * Prepend a (key) + value to the current array.
     *
     * EXAMPLE: <code>
     * a(['fòô' => 'bàř'])->prependImmutable('foo')->getArray(); // [0 => 'foo', 'fòô' => 'bàř']
     * </code>
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object, with the prepended value.</p>
     *
     * @phpstan-param T $value
     * @phpstan-param TKey $key
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function prependImmutable($value, $key = null)
    {
        $generator = function () use ($key, $value): \Generator {
            if ($this->properties !== []) {
                $this->checkType($key, $value);
            }

            if ($key !== null) {
                yield $key => $value;
            } else {
                yield $value;
            }

            /** @noinspection YieldFromCanBeUsedInspection - FP */
            foreach ($this->getGenerator() as $keyOld => $itemOld) {
                yield $keyOld => $itemOld;
            }
        };

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Add a suffix to each key.
     *
     * @param float|int|string $suffix
     *
     * @return static
     *                <p>(Immutable) Return an Arrayy object, with the prepended keys.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
     * @param float|int|string $suffix
     *
     * @return static
     *                <p>(Immutable) Return an Arrayy object, with the prepended values.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
            } elseif (\is_object($item) === true) {
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
     * Return the value of a given key and
     * delete the key.
     *
     * @param int|int[]|string|string[]|null $keyOrKeys
     * @param mixed                          $fallback
     *
     * @return mixed
     */
    public function pull($keyOrKeys = null, $fallback = null)
    {
        if ($keyOrKeys === null) {
            $array = $this->toArray();
            $this->clear();

            return $array;
        }

        if (\is_array($keyOrKeys)) {
            $valueOrValues = [];
            foreach ($keyOrKeys as $key) {
                $valueOrValues[] = $this->get($key, $fallback);
                $this->offsetUnset($key);
            }
        } else {
            $valueOrValues = $this->get($keyOrKeys, $fallback);
            $this->offsetUnset($keyOrKeys);
        }

        return $valueOrValues;
    }

    /**
     * Push one or more values onto the end of array at once.
     *
     * @param mixed ...$args
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object, with pushed elements to the end of array.</p>
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     *
     * @phpstan-param  array<TKey,T> ...$args
     * @phpstan-return static<TKey,T>
     */
    public function push(...$args)
    {
        $this->generatorToArray();

        if (
            $this->checkPropertyTypes
            &&
            $this->properties !== []
        ) {
            foreach ($args as $key => $value) {
                $this->checkType($key, $value);
            }
        }

        \array_push($this->array, ...$args);

        return $this;
    }

    /**
     * Get a random value from the current array.
     *
     * EXAMPLE: <code>
     * a([1, 2, 3, 4])->randomImmutable(2); // e.g.: Arrayy[1, 4]
     * </code>
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<int|array-key,T>
     */
    public function randomImmutable(int $number = null): self
    {
        $this->generatorToArray();

        if ($this->count() === 0) {
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
     * EXAMPLE: <code>
     * $arrayy = A::create([1 => 'one', 2 => 'two']);
     * $arrayy->randomKey(); // e.g. 2
     * </code>
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
     * EXAMPLE: <code>
     * a([1 => 'one', 2 => 'two'])->randomKeys(); // e.g. Arrayy[1, 2]
     * </code>
     *
     * @param int $number <p>The number of keys/indexes (should be <= \count($this->array))</p>
     *
     * @throws \RangeException If array is empty
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function randomKeys(int $number): self
    {
        $this->generatorToArray();

        $count = $this->count();

        if (
            $number === 0
            ||
            $number > $count
        ) {
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
     * EXAMPLE: <code>
     * a([1, 2, 3, 4])->randomMutable(2); // e.g.: Arrayy[1, 4]
     * </code>
     *
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function randomMutable(int $number = null): self
    {
        $this->generatorToArray();

        if ($this->count() === 0) {
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
     * EXAMPLE: <code>
     * a([1 => 'one', 2 => 'two'])->randomValue(); // e.g. 'one'
     * </code>
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
     * EXAMPLE: <code>
     * a([1 => 'one', 2 => 'two'])->randomValues(); // e.g. Arrayy['one', 'two']
     * </code>
     *
     * @param int $number
     *
     * @return static
     *                <p>(Mutable)</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function randomValues(int $number): self
    {
        return $this->randomMutable($number);
    }

    /**
     * Get a random value from an array, with the ability to skew the results.
     *
     * EXAMPLE: <code>
     * a([0 => 3, 1 => 4])->randomWeighted([1 => 4]); // e.g.: Arrayy[4] (has a 66% chance of returning 4)
     * </code>
     *
     * @param array    $array
     * @param int|null $number <p>How many values you will take?</p>
     *
     * @return static<int,mixed>
     *                           <p>(Immutable)</p>
     *
     * @phpstan-param  array<T,int> $array
     * @phpstan-return static<(int|string),T>
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
     * Reduce the current array via callable e.g. anonymous-function and return the end result.
     *
     * EXAMPLE: <code>
     * a([1, 2, 3, 4])->reduce(
     *     function ($carry, $item) {
     *         return $carry * $item;
     *     },
     *     1
     * ); // Arrayy[24]
     * </code>
     *
     * @param callable $callable
     * @param mixed    $initial
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @template T2
     *              <p>The output value type.</p>
     *
     * @phpstan-param callable(T2, T, TKey): T2 $callable
     * @phpstan-param T2                  $initial
     *
     * @phpstan-return static<TKey,T2>
     * @psalm-mutation-free
     */
    public function reduce($callable, $initial = []): self
    {
        foreach ($this->getGenerator() as $key => $value) {
            $initial = $callable($initial, $value, $key);
        }

        return static::create(
            $initial,
            $this->iteratorClass,
            false
        );
    }

    /**
     * @param bool $unique
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<int,mixed>
     * @psalm-mutation-free
     */
    public function reduce_dimension(bool $unique = true): self
    {
        // init
        $result = [];

        foreach ($this->getGenerator() as $val) {
            if (\is_array($val)) {
                $result[] = (new static($val))->reduce_dimension($unique)->toArray();
            } else {
                $result[] = [$val];
            }
        }

        $result = $result === [] ? [] : \array_merge(...$result);

        $resultArrayy = new static($result);

        /**
         * @psalm-suppress ImpureMethodCall - object is already re-created
         * @psalm-suppress InvalidReturnStatement - why?
         */
        return $unique ? $resultArrayy->unique() : $resultArrayy;
    }

    /**
     * Create a numerically re-indexed Arrayy object.
     *
     * EXAMPLE: <code>
     * a([2 => 1, 3 => 2])->reindex(); // Arrayy[0 => 1, 1 => 2]
     * </code>
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object, with re-indexed array-elements.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function reindex(): self
    {
        $this->generatorToArray(false);

        $this->array = \array_values($this->array);

        return $this;
    }

    /**
     * Return all items that fail the truth test.
     *
     * EXAMPLE: <code>
     * $closure = function ($value) {
     *     return $value % 2 !== 0;
     * }
     * a([1, 2, 3, 4])->reject($closure); // Arrayy[1 => 2, 3 => 4]
     * </code>
     *
     * @param \Closure $closure
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param \Closure(T=,TKey=):bool  $closure
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
     * EXAMPLE: <code>
     * a([1 => 'bar', 'foo' => 'foo'])->remove(1); // Arrayy['foo' => 'foo']
     * </code>
     *
     * @param mixed $key
     *
     * @return static
     *                <p>(Mutable)</p>
     *
     * @phpstan-param  TKey $key
     * @phpstan-return static<TKey,T>
     */
    public function remove($key)
    {
        // recursive call
        if (\is_array($key)) {
            foreach ($key as $k) {
                $this->internalRemove($k);
            }

            return static::create(
                $this->toArray(),
                $this->iteratorClass,
                false
            );
        }

        $this->internalRemove($key);

        return static::create(
            $this->toArray(),
            $this->iteratorClass,
            false
        );
    }

    /**
     * alias: for "Arrayy->removeValue()"
     *
     * @param mixed $element
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  T $element
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function removeElement($element)
    {
        return $this->removeValue($element);
    }

    /**
     * Remove the first value from the current array.
     *
     * EXAMPLE: <code>
     * a([1 => 'bar', 'foo' => 'foo'])->removeFirst(); // Arrayy['foo' => 'foo']
     * </code>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function removeFirst(): self
    {
        $tmpArray = $this->toArray();

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
     * EXAMPLE: <code>
     * a([1 => 'bar', 'foo' => 'foo'])->removeLast(); // Arrayy[1 => 'bar']
     * </code>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function removeLast(): self
    {
        $tmpArray = $this->toArray();

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
     * EXAMPLE: <code>
     * a([1 => 'bar', 'foo' => 'foo'])->removeValue('foo'); // Arrayy[1 => 'bar']
     * </code>
     *
     * @param mixed $value
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  T $value
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function removeValue($value): self
    {
        $this->generatorToArray();

        // init
        $isSequentialArray = $this->isSequential();

        foreach ($this->array as $key => $item) {
            if ($item === $value) {
                /** @phpstan-ignore-next-line | "Possibly invalid array key type int|string|TKey.", is this a bug in phpstan? */
                unset($this->array[$key]);
            }
        }

        if ($isSequentialArray) {
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
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function repeat($times): self
    {
        if ($times === 0) {
            return static::create([], $this->iteratorClass);
        }

        return static::create(
            \array_fill(0, (int) $times, $this->toArray()),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Replace a key with a new key/value pair.
     *
     * EXAMPLE: <code>
     * $arrayy = a([1 => 'foo', 2 => 'foo2', 3 => 'bar']);
     * $arrayy->replace(2, 'notfoo', 'notbar'); // Arrayy[1 => 'foo', 'notfoo' => 'notbar', 3 => 'bar']
     * </code>
     *
     * @param mixed $oldKey
     * @param mixed $newKey
     * @param mixed $newValue
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function replace($oldKey, $newKey, $newValue): self
    {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        return $that->remove($oldKey)
            ->set($newKey, $newValue);
    }

    /**
     * Create an array using the current array as values and the other array as keys.
     *
     * EXAMPLE: <code>
     * $firstArray = [
     *     1 => 'one',
     *     2 => 'two',
     *     3 => 'three',
     * ];
     * $secondArray = [
     *     'one' => 1,
     *     1     => 'one',
     *     2     => 2,
     * ];
     * $arrayy = a($firstArray);
     * $arrayy->replaceAllKeys($secondArray); // Arrayy[1 => "one", 'one' => "two", 2 => "three"]
     * </code>
     *
     * @param int[]|string[] $keys <p>An array of keys.</p>
     *
     * @return static
     *                <p>(Immutable) Arrayy object with keys from the other array, empty Arrayy object if the number of elements
     *                for each array isn't equal or if the arrays are empty.
     *                </p>
     *
     * @phpstan-param  array<array-key,TKey> $keys
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function replaceAllKeys(array $keys): self
    {
        $data = \array_combine($keys, $this->toArray());
        if ($data === false) {
            $data = [];
        }

        return static::create(
            $data,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Create an array using the current array as keys and the other array as values.
     *
     * EXAMPLE: <code>
     * $firstArray = [
     *     1 => 'one',
     *     2 => 'two',
     *     3 => 'three',
     * ];
     * $secondArray = [
     *     'one' => 1,
     *     1     => 'one',
     *     2     => 2,
     * ];
     * $arrayy = a($firstArray);
     * $arrayy->replaceAllValues($secondArray); // Arrayy['one' => 1, 'two' => 'one', 'three' => 2]
     * </code>
     *
     * @param array $array <p>An array of values.</p>
     *
     * @return static
     *                <p>(Immutable) Arrayy object with values from the other array, empty Arrayy object if the number of elements
     *                for each array isn't equal or if the arrays are empty.
     *                </p>
     *
     * @phpstan-param  array<array-key,T> $array
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function replaceAllValues(array $array): self
    {
        $data = \array_combine($this->toArray(), $array);
        if ($data === false) {
            $data = [];
        }

        return static::create(
            $data,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Replace the keys in an array with another set.
     *
     * EXAMPLE: <code>
     * a([1 => 'bar', 'foo' => 'foo'])->replaceKeys([1 => 2, 'foo' => 'replaced']); // Arrayy[2 => 'bar', 'replaced' => 'foo']
     * </code>
     *
     * @param array $keys <p>An array of keys matching the array's size.</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<array-key,TKey> $keys
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function replaceKeys(array $keys): self
    {
        $values = \array_values($this->toArray());
        $result = \array_combine($keys, $values);
        if ($result === false) {
            $result = [];
        }

        return static::create(
            $result,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Replace the first matched value in an array.
     *
     * EXAMPLE: <code>
     * $testArray = ['bar', 'foo' => 'foo', 'foobar' => 'foobar'];
     * a($testArray)->replaceOneValue('foo', 'replaced'); // Arrayy['bar', 'foo' => 'replaced', 'foobar' => 'foobar']
     * </code>
     *
     * @param mixed $search      <p>The value to replace.</p>
     * @param mixed $replacement <p>The value to replace.</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function replaceOneValue($search, $replacement = ''): self
    {
        $array = $this->toArray();
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
     * EXAMPLE: <code>
     * $testArray = ['bar', 'foo' => 'foo', 'foobar' => 'foobar'];
     * a($testArray)->replaceValues('foo', 'replaced'); // Arrayy['bar', 'foo' => 'replaced', 'foobar' => 'replacedbar']
     * </code>
     *
     * @param string $search      <p>The value to replace.</p>
     * @param string $replacement <p>What to replace it with.</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function replaceValues($search, $replacement = ''): self
    {
        $function = static function ($value) use ($search, $replacement) {
            return \str_replace($search, $replacement, $value);
        };

        /** @phpstan-ignore-next-line | ignore Closure with one or two parameters, is this a bug in phpstan? */
        return $this->each($function);
    }

    /**
     * Get the last elements from index $from until the end of this array.
     *
     * EXAMPLE: <code>
     * a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->rest(2); // Arrayy[0 => 'lall']
     * </code>
     *
     * @param int $from
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function rest(int $from = 1): self
    {
        $tmpArray = $this->toArray();

        return static::create(
            \array_splice($tmpArray, $from),
            $this->iteratorClass,
            false
        );
    }

    /**
     * Return the array in the reverse order.
     *
     * EXAMPLE: <code>
     * a([1, 2, 3])->reverse(); // self[3, 2, 1]
     * </code>
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
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
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function rsort(int $sort_flags = 0): self
    {
        $this->generatorToArray();

        \rsort($this->array, $sort_flags);

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
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function rsortImmutable(int $sort_flags = 0): self
    {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        $that->rsort($sort_flags);

        return $that;
    }

    /**
     * Search for the first index of the current array via $value.
     *
     * EXAMPLE: <code>
     * a(['fòô' => 'bàř', 'lall' => 'bàř'])->searchIndex('bàř'); // Arrayy[0 => 'fòô']
     * </code>
     *
     * @param mixed $value
     *
     * @return false|float|int|string
     *                                <p>Will return <b>FALSE</b> if the value can't be found.</p>
     * @psalm-mutation-free
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
     * EXAMPLE: <code>
     * a(['fòô' => 'bàř'])->searchValue('fòô'); // Arrayy[0 => 'bàř']
     * </code>
     *
     * @param mixed $index
     *
     * @return static
     *                <p>(Immutable) Will return a empty Arrayy if the value wasn't found.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function searchValue($index): self
    {
        $this->generatorToArray();

        // init
        $return = [];

        if ($this->array === []) {
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

        if ($this->offsetExists($index)) {
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
     * EXAMPLE: <code>
     * $arrayy = a(['Lars' => ['lastname' => 'Moelleken']]);
     * $arrayy->set('Lars.lastname', 'Müller'); // Arrayy['Lars', ['lastname' => 'Müller']]]
     * </code>
     *
     * @param string $key   <p>The key to set.</p>
     * @param mixed  $value <p>Its value.</p>
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-param  TKey $key
     * @phpstan-param  T $value
     * @phpstan-return static<TKey,T>
     */
    public function set($key, $value): self
    {
        $this->internalSet($key, $value);

        return $this;
    }

    /**
     * Get a value from a array and set it if it was not.
     *
     * WARNING: this method only set the value, if the $key is not already set
     *
     * EXAMPLE: <code>
     * $arrayy = a([1 => 1, 2 => 2, 3 => 3]);
     * $arrayy->setAndGet(1, 4); // 1
     * $arrayy->setAndGet(0, 4); // 4
     * </code>
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
            $this->array = $this->set($key, $fallback)->toArray();
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
     * EXAMPLE: <code>
     * a([1 => 'bar', 'foo' => 'foo'])->shuffle(); // e.g.: Arrayy[['foo' => 'foo', 1 => 'bar']]
     * </code>
     *
     * @param bool       $secure <p>using a CSPRNG | @see https://paragonie.com/b/JvICXzh_jhLyt4y3</p>
     * @param array|null $array  [optional]
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<TKey,T> $array
     * @phpstan-return static<TKey,T>
     *
     * @noinspection BadExceptionsProcessingInspection
     * @noinspection NonSecureShuffleUsageInspection
     */
    public function shuffle(bool $secure = false, array $array = null): self
    {
        if ($array === null) {
            $array = $this->toArray(false);
        }

        if ($secure !== true) {
            \shuffle($array);
        } else {
            $size = \count($array, \COUNT_NORMAL);
            $keys = \array_keys($array);
            for ($i = $size - 1; $i > 0; --$i) {
                try {
                    $r = \random_int(0, $i);
                } catch (\Exception $e) {
                    $r = \mt_rand(0, $i);
                }
                if ($r !== $i) {
                    $temp = $array[$keys[$r]];
                    $array[$keys[$r]] = $array[$keys[$i]];
                    $array[$keys[$i]] = $temp;
                }
            }
        }

        foreach ($array as $key => $value) {
            // check if recursive is needed
            if (\is_array($value)) {
                /** @noinspection PhpSillyAssignmentInspection - hack for phpstan */
                /** @phpstan-var array<TKey,T> $value */
                $value = $value;

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
     * Checks whether array has exactly $size items.
     *
     * @param int $size
     *
     * @return bool
     */
    public function sizeIs(int $size): bool
    {
        // init
        $itemsTempCount = 0;

        /** @noinspection PhpUnusedLocalVariableInspection */
        /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
        foreach ($this->getGeneratorByReference() as &$value) {
            ++$itemsTempCount;
            if ($itemsTempCount > $size) {
                return false;
            }
        }

        return $itemsTempCount === $size;
    }

    /**
     * Checks whether array has between $fromSize to $toSize items. $toSize can be
     * smaller than $fromSize.
     *
     * @param int $fromSize
     * @param int $toSize
     *
     * @return bool
     */
    public function sizeIsBetween(int $fromSize, int $toSize): bool
    {
        if ($fromSize > $toSize) {
            $tmp = $toSize;
            $toSize = $fromSize;
            $fromSize = $tmp;
        }

        // init
        $itemsTempCount = 0;

        foreach ($this->getGenerator() as $key => $value) {
            ++$itemsTempCount;
            if ($itemsTempCount > $toSize) {
                return false;
            }
        }

        return $fromSize < $itemsTempCount && $itemsTempCount < $toSize;
    }

    /**
     * Checks whether array has more than $size items.
     *
     * @param int $size
     *
     * @return bool
     */
    public function sizeIsGreaterThan(int $size): bool
    {
        // init
        $itemsTempCount = 0;

        foreach ($this->getGenerator() as $key => $value) {
            ++$itemsTempCount;
            if ($itemsTempCount > $size) {
                return true;
            }
        }

        return $itemsTempCount > $size;
    }

    /**
     * Checks whether array has less than $size items.
     *
     * @param int $size
     *
     * @return bool
     */
    public function sizeIsLessThan(int $size): bool
    {
        // init
        $itemsTempCount = 0;

        foreach ($this->getGenerator() as $key => $value) {
            ++$itemsTempCount;
            if ($itemsTempCount > $size) {
                return false;
            }
        }

        return $itemsTempCount < $size;
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
        return \count($this->toArray(), \COUNT_RECURSIVE);
    }

    /**
     * Extract a slice of the array.
     *
     * @param int      $offset       <p>Slice begin index.</p>
     * @param int|null $length       <p>Length of the slice.</p>
     * @param bool     $preserveKeys <p>Whether array keys are preserved or no.</p>
     *
     * @return static
     *                <p>(Immutable) A slice of the original array with length $length.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function slice(int $offset, int $length = null, bool $preserveKeys = false)
    {
        return static::create(
            \array_slice(
                $this->toArray(),
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
     * EXAMPLE: <code>
     * a(3 => 'd', 2 => 'f', 0 => 'a')->sort(SORT_ASC, SORT_NATURAL, false); // Arrayy[0 => 'a', 1 => 'd', 2 => 'f']
     * </code>
     *
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>sort_flags => use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     * @param bool       $keepKeys
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<int|TKey,T>
     */
    public function sort(
        $direction = \SORT_ASC,
        int $strategy = \SORT_REGULAR,
        bool $keepKeys = false
    ): self {
        $this->generatorToArray();

        return $this->sorting(
            $this->array,
            $direction,
            $strategy,
            $keepKeys
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
     *                <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<int|TKey,T>
     */
    public function sortImmutable(
        $direction = \SORT_ASC,
        int $strategy = \SORT_REGULAR,
        bool $keepKeys = false
    ): self {
        $that = clone $this;

        $that->generatorToArray();

        return $that->sorting(
            $that->array,
            $direction,
            $strategy,
            $keepKeys
        );
    }

    /**
     * Sort the current array by key.
     *
     * EXAMPLE: <code>
     * a([1 => 2, 0 => 1])->sortKeys(\SORT_ASC); // Arrayy[0 => 1, 1 => 2]
     * </code>
     *
     * @see http://php.net/manual/en/function.ksort.php
     * @see http://php.net/manual/en/function.krsort.php
     *
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function sortKeys(
        $direction = \SORT_ASC,
        int $strategy = \SORT_REGULAR
    ): self {
        $this->generatorToArray();

        $this->sorterKeys($this->array, $direction, $strategy);

        return $this;
    }

    /**
     * Sort the current array by key.
     *
     * @see          http://php.net/manual/en/function.ksort.php
     * @see          http://php.net/manual/en/function.krsort.php
     *
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     *
     * @return $this
     *               <p>(Immutable) Return this Arrayy object.</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function sortKeysImmutable(
        $direction = \SORT_ASC,
        int $strategy = \SORT_REGULAR
    ): self {
        $that = clone $this;

        /**
         * @psalm-suppress ImpureMethodCall - object is already cloned
         */
        $that->sortKeys($direction, $strategy);

        return $that;
    }

    /**
     * Sort the current array by value.
     *
     * EXAMPLE: <code>
     * a(3 => 'd', 2 => 'f', 0 => 'a')->sortValueKeepIndex(SORT_ASC, SORT_REGULAR); // Arrayy[0 => 'a', 3 => 'd', 2 => 'f']
     * </code>
     *
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     *
     * @return static
     *                <p>(Mutable)</p>
     *
     * @phpstan-return static<int|TKey,T>
     */
    public function sortValueKeepIndex(
        $direction = \SORT_ASC,
        int $strategy = \SORT_REGULAR
    ): self {
        return $this->sort($direction, $strategy, true);
    }

    /**
     * Sort the current array by value.
     *
     * EXAMPLE: <code>
     * a(3 => 'd', 2 => 'f', 0 => 'a')->sortValueNewIndex(SORT_ASC, SORT_NATURAL); // Arrayy[0 => 'a', 1 => 'd', 2 => 'f']
     * </code>
     *
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     *
     * @return static
     *                <p>(Mutable)</p>
     *
     * @phpstan-return static<int|TKey,T>
     */
    public function sortValueNewIndex($direction = \SORT_ASC, int $strategy = \SORT_REGULAR): self
    {
        return $this->sort($direction, $strategy, false);
    }

    /**
     * Sort a array by value or by a closure.
     *
     * - If the sorter is null, the array is sorted naturally.
     * - Associative (string) keys will be maintained, but numeric keys will be re-indexed.
     *
     * EXAMPLE: <code>
     * $testArray = range(1, 5);
     * $under = a($testArray)->sorter(
     *     function ($value) {
     *         return $value % 2 === 0;
     *     }
     * );
     * var_dump($under); // Arrayy[1, 3, 5, 2, 4]
     * </code>
     *
     * @param callable|mixed|null $sorter
     * @param int|string          $direction <p>use <strong>SORT_ASC</strong> (default) or
     *                                       <strong>SORT_DESC</strong></p>
     * @param int                 $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                                       <strong>SORT_NATURAL</strong></p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @pslam-param callable|T|null $sorter
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function sorter($sorter = null, $direction = \SORT_ASC, int $strategy = \SORT_REGULAR): self
    {
        $array = $this->toArray();
        $direction = $this->getDirection($direction);

        // Transform all values into their results.
        if ($sorter) {
            $arrayy = static::create(
                $array,
                $this->iteratorClass,
                false
            );

            /**
             * @psalm-suppress MissingClosureReturnType
             * @psalm-suppress MissingClosureParamType
             */
            $results = $arrayy->each(
                static function ($value) use ($sorter) {
                    if (\is_callable($sorter) === true) {
                        return $sorter($value);
                    }

                    return $sorter === $value;
                }
            );

            $results = $results->toArray();
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
     * @param int      $offset
     * @param int|null $length
     * @param array    $replacement
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-param  array<mixed,T> $replacement
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function splice(int $offset, int $length = null, $replacement = []): self
    {
        $tmpArray = $this->toArray();

        \array_splice(
            $tmpArray,
            $offset,
            $length ?? $this->count(),
            $replacement
        );

        return static::create(
            $tmpArray,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Split an array in the given amount of pieces.
     *
     * EXAMPLE: <code>
     * a(['a' => 1, 'b' => 2])->split(2, true); // Arrayy[['a' => 1], ['b' => 2]]
     * </code>
     *
     * @param int  $numberOfPieces
     * @param bool $keepKeys
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function split(int $numberOfPieces = 2, bool $keepKeys = false): self
    {
        if ($keepKeys) {
            $generator = function () use ($numberOfPieces) {
                $carry = [];
                $i = 1;
                foreach ($this->getGenerator() as $key => $value) {
                    $carry[$key] = $value;

                    if ($i % $numberOfPieces !== 0) {
                        ++$i;

                        continue;
                    }

                    yield $carry;

                    $carry = [];
                    $i = 1;
                }

                if ($carry !== []) {
                    yield $carry;
                }
            };
        } else {
            $generator = function () use ($numberOfPieces) {
                $carry = [];
                $i = 1;
                foreach ($this->getGenerator() as $key => $value) {
                    $carry[] = $value;

                    if ($i % $numberOfPieces !== 0) {
                        ++$i;

                        continue;
                    }

                    yield $carry;

                    $carry = [];
                    $i = 1;
                }

                if ($carry !== []) {
                    yield $carry;
                }
            };
        }

        return static::create(
            $generator,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Strip all empty items from the current array.
     *
     * EXAMPLE: <code>
     * a(['a' => 1, 'b' => ''])->stripEmpty(); // Arrayy[['a' => 1]]
     * </code>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
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
     * EXAMPLE: <code>
     * a(['a' => 1, 'b' => ''])->swap('a', 'b'); // Arrayy[['a' => '', 'b' => 1]]
     * </code>
     *
     * @param int|string $swapA <p>a key in the array</p>
     * @param int|string $swapB <p>a key in the array</p>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function swap($swapA, $swapB): self
    {
        $array = $this->toArray();

        list($array[$swapA], $array[$swapB]) = [$array[$swapB], $array[$swapA]];

        return static::create(
            $array,
            $this->iteratorClass,
            false
        );
    }

    /**
     * Get the current array from the "Arrayy"-object.
     * alias for "getArray()"
     *
     * @param bool $convertAllArrayyElements <p>
     *                                       Convert all Child-"Arrayy" objects also to arrays.
     *                                       </p>
     * @param bool $preserveKeys             <p>
     *                                       e.g.: A generator maybe return the same key more then once,
     *                                       so maybe you will ignore the keys.
     *                                       </p>
     *
     * @return array
     *
     * @phpstan-return array<TKey,T>
     * @psalm-mutation-free
     */
    public function toArray(
        bool $convertAllArrayyElements = false,
        bool $preserveKeys = true
    ): array {
        // init
        $array = [];

        if ($convertAllArrayyElements) {
            foreach ($this->getGenerator() as $key => $value) {
                if ($value instanceof self) {
                    $value = $value->toArray(
                        $convertAllArrayyElements,
                        $preserveKeys
                    );
                }

                if ($preserveKeys) {
                    $array[$key] = $value;
                } else {
                    $array[] = $value;
                }
            }
        } else {
            $array = \iterator_to_array($this->getGenerator(), $preserveKeys);
        }

        /** @phpstan-ignore-next-line - depends on the $convertAllArrayyElements parameter :/ */
        return $array;
    }

    /**
     * Get the current array from the "Arrayy"-object as list.
     *
     * @param bool $convertAllArrayyElements <p>
     *                                       Convert all Child-"Arrayy" objects also to arrays.
     *                                       </p>
     *
     * @return array
     *
     * @phpstan-return list<mixed>|list<T>
     * @psalm-mutation-free
     */
    public function toList(bool $convertAllArrayyElements = false): array
    {
        return $this->toArray(
            $convertAllArrayyElements,
            false
        );
    }

    /**
     * Convert the current array to JSON.
     *
     * EXAMPLE: <code>
     * a(['bar', ['foo']])->toJson(); // '["bar",{"1":"foo"}]'
     * </code>
     *
     * @param int $options [optional] <p>e.g. JSON_PRETTY_PRINT</p>
     * @param int $depth   [optional] <p>Set the maximum depth. Must be greater than zero.</p>
     *
     * @return string
     */
    public function toJson(int $options = 0, int $depth = 512): string
    {
        $return = \json_encode($this->toArray(), $options, $depth);
        if ($return === false) {
            return '';
        }

        return $return;
    }

    /**
     * @param string[]|null $items  [optional]
     * @param string[]      $helper [optional]
     *
     * @return static|static[]
     *
     * @phpstan-return static<int, static<TKey,T>>
     */
    public function toPermutation(array $items = null, array $helper = []): self
    {
        // init
        $return = [];

        if ($items === null) {
            $items = $this->toArray();
        }

        if (empty($items)) {
            $return[] = $helper;
        } else {
            for ($i = \count($items) - 1; $i >= 0; --$i) {
                $new_items = $items;
                $new_helper = $helper;
                list($tmp_helper) = \array_splice($new_items, $i, 1);
                /** @noinspection PhpSillyAssignmentInspection */
                /** @var string[] $new_items */
                $new_items = $new_items;
                \array_unshift($new_helper, $tmp_helper);
                $return = \array_merge(
                    $return,
                    $this->toPermutation($new_items, $new_helper)->toArray()
                );
            }
        }

        return static::create(
            $return,
            $this->iteratorClass,
            false
        );
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
     * EXAMPLE: <code>
     * a([2 => 1, 3 => 2, 4 => 2])->uniqueNewIndex(); // Arrayy[1, 2]
     * </code>
     *
     * @return $this
     *               <p>(Mutable)</p>
     *
     * @phpstan-return static<int,T>
     */
    public function uniqueNewIndex(): self
    {
        // INFO: \array_unique() can't handle e.g. "stdClass"-values in an array

        $this->array = $this->reduce(
            static function ($resultArray, $value, $key) {
                if (!\in_array($value, $resultArray, true)) {
                    $resultArray[] = $value;
                }

                return $resultArray;
            },
            []
        )->toArray();
        $this->generator = null;

        return $this;
    }

    /**
     * Return a duplicate free copy of the current array. (with the old keys)
     *
     * EXAMPLE: <code>
     * a([2 => 1, 3 => 2, 4 => 2])->uniqueNewIndex(); // Arrayy[2 => 1, 3 => 2]
     * </code>
     *
     * @return $this
     *               <p>(Mutable)</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function uniqueKeepIndex(): self
    {
        // INFO: \array_unique() can't handle e.g. "stdClass"-values in an array

        // init
        $array = $this->toArray();

        /**
         * @psalm-suppress MissingClosureReturnType
         * @psalm-suppress MissingClosureParamType
         */
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

        return $this;
    }

    /**
     * alias: for "Arrayy->uniqueNewIndex()"
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
     *
     * @see          Arrayy::unique()
     *
     * @phpstan-return static<int,T>
     */
    public function unique(): self
    {
        return $this->uniqueNewIndex();
    }

    /**
     * Prepends one or more values to the beginning of array at once.
     *
     * @param mixed ...$args
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object, with prepended elements to the beginning of array.</p>
     *
     * @phpstan-param  array<TKey,T> ...$args
     * @phpstan-return static<TKey,T>
     */
    public function unshift(...$args): self
    {
        $this->generatorToArray();

        if (
            $this->checkPropertyTypes
            &&
            $this->properties !== []
        ) {
            foreach ($args as $key => $value) {
                $this->checkType($key, $value);
            }
        }

        \array_unshift($this->array, ...$args);

        return $this;
    }

    /**
     * Tests whether the given closure return something valid for all elements of this array.
     *
     * @param \Closure $closure the predicate
     *
     * @return bool
     *              <p>TRUE, if the predicate yields TRUE for all elements, FALSE otherwise.</p>
     *
     * @phpstan-param \Closure(T=,TKey=):bool $closure
     */
    public function validate(\Closure $closure): bool
    {
        foreach ($this->getGenerator() as $key => $value) {
            if (!$closure($value, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all values from a array.
     *
     * EXAMPLE: <code>
     * $arrayy = a([1 => 'foo', 2 => 'foo2', 3 => 'bar']);
     * $arrayyTmp->values(); // Arrayy[0 => 'foo', 1 => 'foo2', 2 => 'bar']
     * </code>
     *
     * @return static
     *                <p>(Immutable)</p>
     *
     * @phpstan-return static<TKey,T>
     * @psalm-mutation-free
     */
    public function values(): self
    {
        return static::create(
            function () {
                /** @noinspection YieldFromCanBeUsedInspection */
                foreach ($this->getGenerator() as $value) {
                    yield $value;
                }
            },
            $this->iteratorClass,
            false
        );
    }

    /**
     * Apply the given function to every element in the array, discarding the results.
     *
     * EXAMPLE: <code>
     * $callable = function (&$value, $key) {
     *     $value = $key;
     * };
     * $arrayy = a([1, 2, 3]);
     * $arrayy->walk($callable); // Arrayy[0, 1, 2]
     * </code>
     *
     * @param callable $callable
     * @param bool     $recursive [optional] <p>Whether array will be walked recursively or no</p>
     * @param mixed    $userData  [optional] <p>
     *                            If the optional $userData parameter is supplied,
     *                            it will be passed as the third parameter to the $callable.
     *                            </p>
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object, with modified elements.</p>
     *
     * @phpstan-return static<TKey,T>
     */
    public function walk(
        $callable,
        bool $recursive = false,
        $userData = self::ARRAYY_HELPER_WALK
    ): self {
        $this->generatorToArray();

        if ($this->array !== []) {
            if ($recursive === true) {
                if ($userData !== self::ARRAYY_HELPER_WALK) {
                    \array_walk_recursive($this->array, $callable, $userData);
                } else {
                    \array_walk_recursive($this->array, $callable);
                }
            } else {
                /** @noinspection NestedPositiveIfStatementsInspection */
                if ($userData !== self::ARRAYY_HELPER_WALK) {
                    \array_walk($this->array, $callable, $userData);
                } else {
                    \array_walk($this->array, $callable);
                }
            }
        }

        return $this;
    }

    /**
     * Returns a collection of matching items.
     *
     * @param string $keyOrPropertyOrMethod the property or method to evaluate
     * @param mixed  $value                 the value to match
     *
     * @throws \InvalidArgumentException if property or method is not defined
     *
     * @return static
     *
     * @phpstan-return static<TKey,T>
     */
    public function where(string $keyOrPropertyOrMethod, $value): self
    {
        return $this->filter(
            function ($item) use ($keyOrPropertyOrMethod, $value) {
                $accessorValue = $this->extractValue(
                    $item,
                    $keyOrPropertyOrMethod
                );

                return $accessorValue === $value;
            }
        );
    }

    /**
     * Convert an array into a object.
     *
     * @param array $array
     *
     * @return \stdClass
     *
     * @phpstan-param array<int|string,mixed> $array
     */
    final protected static function arrayToObject(array $array = []): \stdClass
    {
        // init
        $object = new \stdClass();

        if (\count($array, \COUNT_NORMAL) <= 0) {
            return $object;
        }

        foreach ($array as $name => $value) {
            if (\is_array($value)) {
                $object->{$name} = static::arrayToObject($value);
            } else {
                $object->{$name} = $value;
            }
        }

        return $object;
    }

    /**
     * @param array|\Generator|null $input         <p>
     *                                             An array containing keys to return.
     *                                             </p>
     * @param mixed|null            $search_values [optional] <p>
     *                                             If specified, then only keys containing these values are returned.
     *                                             </p>
     * @param bool                  $strict        [optional] <p>
     *                                             Determines if strict comparison (===) should be used during the
     *                                             search.
     *                                             </p>
     *
     * @return array
     *               <p>An array of all the keys in input.</p>
     *
     * @phpstan-param  array<mixed>|null $input
     * @phpstan-return array<mixed>
     * @psalm-mutation-free
     */
    protected function array_keys_recursive(
        $input = null,
        $search_values = null,
        bool $strict = true
    ): array {
        // init
        $keys = [];
        $keysTmp = [];

        if ($input === null) {
            $input = $this->getGenerator();
        }

        if ($search_values === null) {
            foreach ($input as $key => $value) {
                $keys[] = $key;

                // check if recursive is needed
                if (\is_array($value)) {
                    $keysTmp[] = $this->array_keys_recursive($value);
                }
            }
        } else {
            $is_array_tmp = \is_array($search_values);

            foreach ($input as $key => $value) {
                if (
                    (
                        $is_array_tmp === false
                        &&
                        $strict === true
                        &&
                        $search_values === $value
                    )
                    ||
                    (
                        $is_array_tmp === false
                        &&
                        $strict === false
                        &&
                        $search_values == $value
                    )
                    ||
                    (
                        $is_array_tmp === true
                        &&
                        \in_array($value, $search_values, $strict)
                    )
                ) {
                    $keys[] = $key;
                }

                // check if recursive is needed
                if (\is_array($value)) {
                    $keysTmp[] = $this->array_keys_recursive($value);
                }
            }
        }

        return $keysTmp === [] ? $keys : \array_merge($keys, ...$keysTmp);
    }

    /**
     * @param mixed      $path
     * @param callable   $callable
     * @param array|null $currentOffset
     *
     * @return void
     *
     * @phpstan-param array<TKey,T>|null $currentOffset
     * @psalm-mutation-free
     */
    protected function callAtPath($path, $callable, &$currentOffset = null)
    {
        $this->generatorToArray();

        if ($currentOffset === null) {
            $currentOffset = &$this->array;
        }

        $explodedPath = \explode($this->pathSeparator, $path);
        if ($explodedPath === false) {
            return;
        }

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
     * Extracts the value of the given property or method from the object.
     *
     * @param static $object                <p>The object to extract the value from.</p>
     * @param string $keyOrPropertyOrMethod <p>The property or method for which the
     *                                      value should be extracted.</p>
     *
     * @throws \InvalidArgumentException if the method or property is not defined
     *
     * @return mixed
     *               <p>The value extracted from the specified property or method.</p>
     *
     * @phpstan-param self<TKey,T> $object
     */
    final protected function extractValue(self $object, string $keyOrPropertyOrMethod)
    {
        if (isset($object[$keyOrPropertyOrMethod])) {
            $return = $object->get($keyOrPropertyOrMethod);

            if ($return instanceof self) {
                return $return->toArray();
            }

            return $return;
        }

        if (\property_exists($object, $keyOrPropertyOrMethod)) {
            return $object->{$keyOrPropertyOrMethod};
        }

        if (\method_exists($object, $keyOrPropertyOrMethod)) {
            return $object->{$keyOrPropertyOrMethod}();
        }

        throw new \InvalidArgumentException(\sprintf('array-key & property & method "%s" not defined in %s', $keyOrPropertyOrMethod, \gettype($object)));
    }

    /**
     * create a fallback for array
     *
     * 1. use the current array, if it's a array
     * 2. fallback to empty array, if there is nothing
     * 3. call "getArray()" on object, if there is a "Arrayy"-object
     * 4. call "createFromObject()" on object, if there is a "\Traversable"-object
     * 5. call "__toArray()" on object, if the method exists
     * 6. cast a string or object with "__toString()" into an array
     * 7. throw a "InvalidArgumentException"-Exception
     *
     * @param mixed $data
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     *
     * @phpstan-return array<mixed>|array<TKey,T>
     */
    protected function fallbackForArray(&$data): array
    {
        $data = $this->internalGetArray($data);

        if ($data === null) {
            throw new \InvalidArgumentException('Passed value should be a array');
        }

        return $data;
    }

    /**
     * @param bool $preserveKeys <p>
     *                           e.g.: A generator maybe return the same key more then once,
     *                           so maybe you will ignore the keys.
     *                           </p>
     *
     * @return bool
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @psalm-mutation-free :/
     */
    protected function generatorToArray(bool $preserveKeys = true)
    {
        if ($this->generator) {
            $this->array = $this->toArray(false, $preserveKeys);
            $this->generator = null;

            return true;
        }

        return false;
    }

    /**
     * Get correct PHP constant for direction.
     *
     * @param int|string $direction
     *
     * @return int
     * @psalm-mutation-free
     */
    protected function getDirection($direction): int
    {
        if ((string) $direction === $direction) {
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
     * @return TypeCheckInterface[]
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    protected function getPropertiesFromPhpDoc()
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
        $docComment = $reflector->getDocComment();
        if ($docComment) {
            $docblock = $factory->create($docComment);
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Property $tag */
            foreach ($docblock->getTagsByName('property') as $tag) {
                $typeName = $tag->getVariableName();
                /** @var string|null $typeName */
                if ($typeName !== null) {
                    $typeCheckPhpDoc = TypeCheckPhpDoc::fromPhpDocumentorProperty($tag, $typeName);
                    if ($typeCheckPhpDoc !== null) {
                        $properties[$typeName] = $typeCheckPhpDoc;
                    }
                }
            }
        }

        /** @noinspection PhpAssignmentInConditionInspection */
        while ($reflector = $reflector->getParentClass()) {
            $docComment = $reflector->getDocComment();
            if ($docComment) {
                $docblock = $factory->create($docComment);
                /** @var \phpDocumentor\Reflection\DocBlock\Tags\Property $tag */
                foreach ($docblock->getTagsByName('property') as $tag) {
                    $typeName = $tag->getVariableName();
                    /** @var string|null $typeName */
                    if ($typeName !== null) {
                        if (isset($properties[$typeName])) {
                            continue;
                        }

                        $typeCheckPhpDoc = TypeCheckPhpDoc::fromPhpDocumentorProperty($tag, $typeName);
                        if ($typeCheckPhpDoc !== null) {
                            $properties[$typeName] = $typeCheckPhpDoc;
                        }
                    }
                }
            }
        }

        return $PROPERTY_CACHE[$cacheKey] = $properties;
    }

    /**
     * @param mixed $glue
     * @param mixed $pieces
     * @param bool  $useKeys
     *
     * @return string
     *
     * @phpstan-param scalar|object|self<TKey|T>|array<TKey,T> $pieces
     * @psalm-mutation-free
     */
    protected function implode_recursive(
        $glue = '',
        $pieces = [],
        bool $useKeys = false
    ): string {
        if ($pieces instanceof self) {
            $pieces = $pieces->toArray();
        }

        if (\is_array($pieces)) {
            /** @noinspection PhpSillyAssignmentInspection - hack for phpstan */
            /** @phpstan-var array<TKey,T> $pieces */
            $pieces = $pieces;

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

        if (
            \is_scalar($pieces) === true
            ||
            (\is_object($pieces) && \method_exists($pieces, '__toString'))
        ) {
            return (string) $pieces;
        }

        return '';
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
     *
     * @phpstan-param (array&T)|array<TKey,T>|\Generator<TKey,T>|null $haystack
     * @psalm-mutation-free
     */
    protected function in_array_recursive($needle, $haystack = null, $strict = true): bool
    {
        if ($haystack === null) {
            $haystack = $this->getGenerator();
        }

        foreach ($haystack as $item) {
            if (\is_array($item)) {
                $returnTmp = $this->in_array_recursive($needle, $item, $strict);
            } else {
                /** @noinspection NestedPositiveIfStatementsInspection */
                if ($strict === true) {
                    $returnTmp = $item === $needle;
                } else {
                    $returnTmp = $item == $needle;
                }
            }

            if ($returnTmp === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $data
     *
     * @return array<mixed>|null
     */
    protected function internalGetArray(&$data)
    {
        if (\is_array($data)) {
            return $data;
        }

        if (!$data) {
            return [];
        }

        if (\is_object($data) === true) {
            if ($data instanceof \ArrayObject) {
                return $data->getArrayCopy();
            }

            if ($data instanceof \Generator) {
                return static::createFromGeneratorImmutable($data)->toArray();
            }

            if ($data instanceof \Traversable) {
                return static::createFromObject($data)->toArray();
            }

            if ($data instanceof \JsonSerializable) {
                return (array) $data->jsonSerialize();
            }

            if (\method_exists($data, '__toArray')) {
                return (array) $data->__toArray();
            }

            if (\method_exists($data, '__toString')) {
                return [(string) $data];
            }
        }

        if (\is_callable($data)) {
            /**
             * @psalm-suppress InvalidPropertyAssignmentValue - why?
             */
            $this->generator = new ArrayyRewindableGenerator($data);

            return [];
        }

        if (\is_scalar($data)) {
            return [$data];
        }

        return null;
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

        if (
            $this->pathSeparator
            &&
            (string) $key === $key
            &&
            \strpos($key, $this->pathSeparator) !== false
        ) {
            $path = \explode($this->pathSeparator, (string) $key);

            if ($path !== false) {
                // crawl though the keys
                while (\count($path, \COUNT_NORMAL) > 1) {
                    $key = \array_shift($path);

                    if (!$this->has($key)) {
                        return false;
                    }

                    $this->array = &$this->array[$key];
                }

                $key = \array_shift($path);
            }
        }

        unset($this->array[$key]);

        return true;
    }

    /**
     * Internal mechanic of set method.
     *
     * @param int|string|null $key
     * @param mixed           $value
     * @param bool            $checkProperties
     *
     * @return bool
     */
    protected function internalSet(
        $key,
        &$value,
        bool $checkProperties = true
    ): bool {
        if (
            $checkProperties === true
            &&
            $this->properties !== []
        ) {
            $this->checkType($key, $value);
        }

        if ($key === null) {
            return false;
        }

        $this->generatorToArray();

        /** @phpstan-var array<int|string,mixed> $array */
        $array = &$this->array;

        /**
         * https://github.com/vimeo/psalm/issues/2536
         *
         * @psalm-suppress PossiblyInvalidArgument
         * @psalm-suppress InvalidScalarArgument
         */
        if (
            $this->pathSeparator
            &&
            (string) $key === $key
            &&
            \strpos($key, $this->pathSeparator) !== false
        ) {
            $path = \explode($this->pathSeparator, (string) $key);

            if ($path !== false) {
                // crawl through the keys
                while (\count($path, \COUNT_NORMAL) > 1) {
                    $key = \array_shift($path);

                    $array = &$array[$key];
                }

                $key = \array_shift($path);
            }
        }

        if ($array === null) {
            $array = [];
        } elseif (!\is_array($array)) {
            throw new \RuntimeException('Can not set value at this path "' . $key . '" because (' . \gettype($array) . ')"' . \print_r($array, true) . '" is not an array.');
        }

        $array[$key] = $value;

        return true;
    }

    /**
     * Convert a object into an array.
     *
     * @param mixed|object $object
     *
     * @return array|mixed
     *
     * @psalm-mutation-free
     */
    protected static function objectToArray($object)
    {
        if (!\is_object($object)) {
            return $object;
        }

        $object = \get_object_vars($object);

        /**
         * @psalm-suppress PossiblyInvalidArgument - the parameter is always some kind of array - false-positive from psalm?
         */
        return \array_map(['static', 'objectToArray'], $object);
    }

    /**
     * @param array $data
     * @param bool  $checkPropertiesInConstructor
     *
     * @return void
     *
     * @phpstan-param array<mixed,T> $data
     */
    protected function setInitialValuesAndProperties(array &$data, bool $checkPropertiesInConstructor)
    {
        $checkPropertiesInConstructor = $this->checkForMissingPropertiesInConstructor === true
                                        &&
                                        $checkPropertiesInConstructor === true;

        if ($this->properties !== []) {
            foreach ($data as $key => &$valueInner) {
                $this->internalSet(
                    $key,
                    $valueInner,
                    $checkPropertiesInConstructor
                );
            }
        } else {
            if (
                $this->checkPropertyTypes === true
                ||
                $checkPropertiesInConstructor === true
            ) {
                $this->properties = $this->getPropertiesFromPhpDoc();
            }

            /** @var TypeCheckInterface[] $properties */
            $properties = $this->properties;

            if (
                $this->checkPropertiesMismatchInConstructor === true
                &&
                \count($data) !== 0
                &&
                \count(\array_diff_key($properties, $data)) > 0
            ) {
                throw new \TypeError('Property mismatch - input: ' . \print_r(\array_keys($data), true) . ' | expected: ' . \print_r(\array_keys($properties), true));
            }

            foreach ($data as $key => &$valueInner) {
                $this->internalSet(
                    $key,
                    $valueInner,
                    $checkPropertiesInConstructor
                );
            }
        }
    }

    /**
     * sorting keys
     *
     * @param array      $elements
     * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
     * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
     *                              <strong>SORT_NATURAL</strong></p>
     *
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-param  array<mixed|TKey,T> $elements
     * @phpstan-return static<TKey,T>
     */
    protected function sorterKeys(
        array &$elements,
        $direction = \SORT_ASC,
        int $strategy = \SORT_REGULAR
    ): self {
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
     * @return $this
     *               <p>(Mutable) Return this Arrayy object.</p>
     *
     * @phpstan-param array<mixed|TKey,T> $elements
     * @phpstan-return static<int|TKey,T>
     */
    protected function sorting(
        array &$elements,
        $direction = \SORT_ASC,
        int $strategy = \SORT_REGULAR,
        bool $keepKeys = false
    ): self {
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
     * @param array $array
     *
     * @return array
     *
     * @psalm-mutation-free
     */
    private function getArrayRecursiveHelperArrayy(array $array)
    {
        if ($array === []) {
            return [];
        }

        \array_walk_recursive(
            $array,
            /**
             * @param array|self $item
             *
             * @return void
             */
            static function (&$item) {
                if ($item instanceof self) {
                    $item = $item->getArray();
                }
            }
        );

        return $array;
    }

    /**
     * @param int|string|null $key
     * @param mixed           $value
     *
     * @return void
     */
    private function checkType($key, $value)
    {
        if (
            $key !== null
            &&
            isset($this->properties[$key]) === false
            &&
            $this->checkPropertiesMismatch === true
        ) {
            throw new \TypeError('The key "' . $key . '" does not exists as "@property" phpdoc. (' . \get_class($this) . ').');
        }

        if (isset($this->properties[self::ARRAYY_HELPER_TYPES_FOR_ALL_PROPERTIES])) {
            $this->properties[self::ARRAYY_HELPER_TYPES_FOR_ALL_PROPERTIES]->checkType($value);
        } elseif ($key !== null && isset($this->properties[$key])) {
            $this->properties[$key]->checkType($value);
        }
    }
}
