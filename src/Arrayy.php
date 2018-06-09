<?php

declare(strict_types=1);

namespace Arrayy;

use voku\helper\UTF8;

/** @noinspection ClassReImplementsParentInterfaceInspection */

/**
 * Methods to manage arrays.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Arrayy extends \ArrayObject implements \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{
  /**
   * @var array
   */
  protected $array = [];

  /**
   * @var string
   */
  protected $iteratorClass = ArrayyIterator::class;

  /**
   * @var string
   */
  protected $pathSeparator = '.';

  /** @noinspection MagicMethodsValidityInspection */
  /**
   * Initializes
   *
   * @param mixed  $array <p>Should be an array, otherwise it will try to convert it into an array.</p>
   * @param string $iteratorClass
   */
  public function __construct($array = [], $iteratorClass = ArrayyIterator::class)
  {
    $array = $this->fallbackForArray($array);
    $this->array = $array;

    $this->setIteratorClass($iteratorClass);
  }

  /**
   * Get a value by key.
   *
   * @param mixed $key
   *
   * @return mixed <p>Get a Value from the current array.</p>
   */
  public function &__get($key)
  {
    $return = $this->get($key);

    if (\is_array($return)) {
      return static::create($return);
    }

    return $return;
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
      if (isset($this->array[$key])) {
        return $this->array[$key];
      }

      return false;
    }

    return (array)$this->array;
  }

  /**
   * Whether or not an element exists by key.
   *
   * @param mixed $key
   *
   * @return bool <p>True is the key/index exists, otherwise false.</p>
   */
  public function __isset($key)
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
  public function __toString()
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
   * alias: for "Arrayy->append()"
   *
   * @see Arrayy::append()
   *
   * @param mixed $value
   *
   * @return static <p>(Mutable) Return this Arrayy object, with the appended values.</p>
   */
  public function add($value)
  {
    return $this->append($value);
  }

  /**
   * Append a (key) + value to the current array.
   *
   * @param mixed $value
   * @param mixed $key
   *
   * @return static <p>(Mutable) Return this Arrayy object, with the appended values.</p>
   */
  public function append($value, $key = null)
  {
    if ($key !== null) {
      $this->array[$key] = $value;
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
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function asort(int $sort_flags = 0)
  {
    \asort($this->array, $sort_flags);

    return $this;
  }

  /**
   * Counts all elements in an array, or something in an object.
   * <p>For objects, if you have SPL installed, you can hook into count() by implementing interface {@see Countable}.
   * The interface has exactly one method, {@see Countable::count()}, which returns the return value for the count()
   * function. Please see the {@see Array} section of the manual for a detailed explanation of how arrays are
   * implemented and used in PHP.
   *
   * @link http://php.net/manual/en/function.count.php
   *
   * @param int $mode [optional] If the optional mode parameter is set to
   *                  COUNT_RECURSIVE (or 1), count
   *                  will recursively count the array. This is particularly useful for
   *                  counting all the elements of a multidimensional array. count does not detect infinite recursion.
   *
   * @return int the number of elements in var, which is
   * typically an array, since anything else will have one
   * element.
   * </p>
   * <p>
   * If var is not an array or an object with
   * implemented Countable interface,
   * 1 will be returned.
   * There is one exception, if var is &null;,
   * 0 will be returned.
   * </p>
   * <p>
   * Caution: count may return 0 for a variable that isn't set,
   * but it may also return 0 for a variable that has been initialized with an
   * empty array. Use isset to test if a variable is set.
   *
   * @return int
   */
  public function count(int $mode = COUNT_NORMAL): int
  {
    return \count($this->array, $mode);
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
    return $this->array;
  }

  /**
   * Returns a new ArrayyIterator, thus implementing the \ArrayIterator interface.
   *
   * @return \ArrayIterator <p>An iterator for the values in the array.</p>
   */
  public function getIterator(): \ArrayIterator
  {
    $iterator = $this->getIteratorClass();

    return new $iterator($this->array);
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
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function ksort(int $sort_flags = 0)
  {
    \ksort($this->array, $sort_flags);

    return $this;
  }

  /**
   * Sort an array using a case insensitive "natural order" algorithm
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function natcasesort()
  {
    \natcasesort($this->array);

    return $this;
  }

  /**
   * Sort entries using a "natural order" algorithm
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function natsort()
  {
    \natsort($this->array);

    return $this;
  }

  /**
   * Whether or not an offset exists.
   *
   * @param int|float|string $offset
   *
   * @return bool
   */
  public function offsetExists($offset): bool
  {
    if ($this->isEmpty()) {
      return false;
    }

    // php cast "bool"-index into "int"-index
    if ((bool)$offset === $offset) {
      $offset = (int)$offset;
    }

    $tmpReturn = \array_key_exists($offset, $this->array);

    if (
        $tmpReturn === true
        ||
        (
            $tmpReturn === false
            &&
            \strpos((string)$offset, $this->pathSeparator) === false
        )
    ) {
      return $tmpReturn;
    }

    $offsetExists = false;

    if (\strpos((string)$offset, $this->pathSeparator) !== false) {

      $offsetExists = false;
      $explodedPath = \explode($this->pathSeparator, (string)$offset);
      $lastOffset = \array_pop($explodedPath);
      $containerPath = \implode($this->pathSeparator, $explodedPath);

      $this->callAtPath(
          $containerPath,
          function ($container) use ($lastOffset, &$offsetExists) {
            $offsetExists = \array_key_exists($lastOffset, $container);
          }
      );
    }

    return $offsetExists;
  }

  /**
   * Returns the value at specified offset.
   *
   * @param int|float|string $offset
   *
   * @return mixed <p>Will return null if the offset did not exists.</p>
   */
  public function offsetGet($offset)
  {
    return $this->offsetExists($offset) ? $this->get($offset) : null;
  }

  /**
   * Assigns a value to the specified offset.
   *
   * @param int|float|string $offset
   * @param mixed            $value
   */
  public function offsetSet($offset, $value)
  {
    if ($offset === null) {
      $this->array[] = $value;
    } else {
      $this->internalSet($offset, $value);
    }
  }

  /**
   * Unset an offset.
   *
   * @param int|float|string $offset
   */
  public function offsetUnset($offset)
  {
    if ($this->isEmpty()) {
      return;
    }

    if (\array_key_exists($offset, $this->array)) {
      unset($this->array[$offset]);

      return;
    }

    if (\strpos((string)$offset, $this->pathSeparator) !== false) {

      $path = \explode($this->pathSeparator, (string)$offset);
      $pathToUnset = \array_pop($path);

      $this->callAtPath(
          \implode($this->pathSeparator, $path),
          function (&$offset) use ($pathToUnset) {
            unset($offset[$pathToUnset]);
          }
      );

    }
  }

  /**
   * Serialize the current "Arrayy"-object.
   *
   * @return string
   */
  public function serialize()
  {
    return parent::serialize();
  }

  /**
   * Sets the iterator classname for the current "Arrayy"-object.
   *
   * @param string $class
   *
   * @return void
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
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   *
   * @throws \InvalidArgumentException
   */
  public function uasort($function)
  {
    if (!\is_callable($function)) {
      throw new \InvalidArgumentException(
          'Passed function must be callable'
      );
    }

    \uasort($this->array, $function);

    return $this;
  }

  /**
   * Sort the entries by keys using a user-defined comparison function.
   *
   * @param \callable $function
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   *
   * @throws \InvalidArgumentException
   */
  public function uksort($function)
  {
    return $this->customSortKeys($function);
  }

  /**
   * Unserialize an string and return this object.
   *
   * @param string $string
   *
   * @return static <p>(Mutable)</p>
   */
  public function unserialize($string)
  {
    parent::unserialize($string);

    return $this;
  }

  /**
   * Add a suffix to each key.
   *
   * @param mixed $prefix
   *
   * @return static <p>(Immutable) Return an Arrayy object, with the prefixed keys.</p>
   */
  public function appendToEachKey($prefix)
  {
    $result = [];
    foreach ($this->array as $key => $item) {
      if ($item instanceof self) {
        $result[$prefix . $key] = $item->appendToEachKey($prefix);
      } elseif (\is_array($item)) {
        $result[$prefix . $key] = self::create($item)->appendToEachKey($prefix)->toArray();
      } else {
        $result[$prefix . $key] = $item;
      }
    }

    return self::create($result);
  }

  /**
   * Add a prefix to each value.
   *
   * @param mixed $prefix
   *
   * @return static <p>(Immutable) Return an Arrayy object, with the prefixed values.</p>
   */
  public function appendToEachValue($prefix)
  {
    $result = [];
    foreach ($this->array as $key => $item) {
      if ($item instanceof self) {
        $result[$key] = $item->appendToEachValue($prefix);
      } elseif (\is_array($item)) {
        $result[$key] = self::create($item)->appendToEachValue($prefix)->toArray();
      } elseif (\is_object($item)) {
        $result[$key] = $item;
      } else {
        $result[$key] = $prefix . $item;
      }
    }

    return self::create($result);
  }

  /**
   * Convert an array into a object.
   *
   * @param array $array PHP array
   *
   * @return \stdClass (object)
   */
  protected static function arrayToObject(array $array = []): \stdClass
  {
    $object = new \stdClass();

    if (!\is_array($array) || \count($array, COUNT_NORMAL) <= 0) {
      return $object;
    }

    foreach ($array as $name => $value) {
      if (\is_array($value)) {
        $object->{$name} = self::arrayToObject($value);
        continue;
      }
      $object->{$name} = $value;
    }

    return $object;
  }

  /**
   * @param array $input        <p>
   *                            An array containing keys to return.
   *                            </p>
   * @param mixed $search_value [optional] <p>
   *                            If specified, then only keys containing these values are returned.
   *                            </p>
   * @param bool  $strict       [optional] <p>
   *                            Determines if strict comparison (===) should be used during the search.
   *                            </p>
   *
   * @return array an array of all the keys in input.
   */
  protected function array_keys_recursive(array $input = null, $search_value = null, bool $strict = true): array
  {
    // init
    $keys = [];

    if ($input === null) {
      $input = $this->array;
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
        $keys = \array_merge($keys, $this->array_keys_recursive($value));
      }
    }

    return $keys;
  }

  /**
   * Sort an array in reverse order and maintain index association.
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function arsort()
  {
    \arsort($this->array);

    return $this;
  }

  /**
   * Iterate over the current array and execute a callback for each loop.
   *
   * @param \Closure $closure
   *
   * @return static <p>(Immutable)</p>
   */
  public function at(\Closure $closure)
  {
    $array = $this->array;

    foreach ($array as $key => $value) {
      $closure($value, $key);
    }

    return static::create($array);
  }

  /**
   * Returns the average value of the current array.
   *
   * @param int $decimals <p>The number of decimal-numbers to return.</p>
   *
   * @return int|double <p>The average value.</p>
   */
  public function average($decimals = 0)
  {
    $count = \count($this->array, COUNT_NORMAL);

    if (!$count) {
      return 0;
    }

    if (!\is_int($decimals)) {
      $decimals = 0;
    }

    return \round(\array_sum($this->array) / $count, $decimals);
  }

  /**
   * @param mixed      $path
   * @param \callable  $callable
   * @param null|array $currentOffset
   */
  protected function callAtPath($path, $callable, &$currentOffset = null)
  {
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
   * Changes all keys in an array.
   *
   * @param int $case [optional] <p> Either <strong>CASE_UPPER</strong><br />
   *                  or <strong>CASE_LOWER</strong> (default)</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function changeKeyCase(int $case = CASE_LOWER)
  {
    return static::create(UTF8::array_change_key_case($this->array, $case));
  }

  /**
   * Change the path separator of the array wrapper.
   *
   * By default, the separator is: "."
   *
   * @param string $separator <p>Separator to set.</p>
   *
   * @return static <p>Mutable</p>
   */
  public function changeSeparator($separator)
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
   * @return static <p>(Immutable) A new array of chunks from the original array.</p>
   */
  public function chunk($size, $preserveKeys = false)
  {
    $result = \array_chunk($this->array, $size, $preserveKeys);

    return static::create($result);
  }

  /**
   * Clean all falsy values from the current array.
   *
   * @return static <p>(Immutable)</p>
   */
  public function clean()
  {
    return $this->filter(
        function ($value) {
          return (bool)$value;
        }
    );
  }

  /**
   * WARNING!!! -> Clear the current array.
   *
   * @return static <p>(Mutable) Return this Arrayy object, with an empty array.</p>
   */
  public function clear()
  {
    $this->array = [];

    return $this;
  }

  /**
   * Check if an item is in the current array.
   *
   * @param string|int|float $value
   * @param bool             $recursive
   * @param bool             $strict
   *
   * @return bool
   */
  public function contains($value, $recursive = false, $strict = true): bool
  {
    if ($recursive === true) {
      return $this->in_array_recursive($value, $this->array, $strict);
    }

    return \in_array($value, $this->array, $strict);
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
      return $this->in_array_recursive(
          UTF8::strtoupper($value),
          $this->walk(
              function (&$val) {
                $val = UTF8::strtoupper($val);
              },
              true
          )->getArray(),
          true
      );
    }

    return \in_array(
        UTF8::strtoupper($value),
        $this->walk(
            function (&$val) {
              $val = UTF8::strtoupper($val);
            },
            false
        )->getArray(),
        true
    );
  }

  /**
   * Check if the given key/index exists in the array.
   *
   * @param string|int|float $key <p>key/index to search for</p>
   *
   * @return bool <p>Returns true if the given key/index exists in the array, false otherwise.</p>
   */
  public function containsKey($key): bool
  {
    return $this->offsetExists($key);
  }

  /**
   * Check if all given needles are present in the array as key/index.
   *
   * @param array $needles <p>The keys you are searching for.</p>
   * @param bool  $recursive
   *
   * @return bool <p>Returns true if all the given keys/indexes exists in the array, false otherwise.</p>
   */
  public function containsKeys(array $needles, $recursive = false): bool
  {
    if ($recursive === true) {
      return \count(
                 \array_intersect($needles, $this->keys(true)->getArray()),
                 COUNT_RECURSIVE
             )
             ===
             \count(
                 $needles,
                 COUNT_RECURSIVE
             );
    }

    return \count(
               \array_intersect($needles, $this->keys()->getArray()),
               COUNT_NORMAL
           )
           ===
           \count(
               $needles,
               COUNT_NORMAL
           );
  }

  /**
   * Check if all given needles are present in the array as key/index.
   *
   * @param array $needles <p>The keys you are searching for.</p>
   *
   * @return bool <p>Returns true if all the given keys/indexes exists in the array, false otherwise.</p>
   */
  public function containsKeysRecursive(array $needles): bool
  {
    return $this->containsKeys($needles, true);
  }

  /**
   * alias: for "Arrayy->contains()"
   *
   * @see Arrayy::contains()
   *
   * @param string|int|float $value
   *
   * @return bool
   */
  public function containsValue($value): bool
  {
    return $this->contains($value);
  }

  /**
   * alias: for "Arrayy->contains($value, true)"
   *
   * @see Arrayy::contains()
   *
   * @param string|int|float $value
   *
   * @return bool
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
   * @return bool <p>Returns true if all the given values exists in the array, false otherwise.</p>
   */
  public function containsValues(array $needles): bool
  {
    return \count(\array_intersect($needles, $this->array), COUNT_NORMAL)
           ===
           \count($needles, COUNT_NORMAL);
  }

  /**
   * Counts all the values of an array
   *
   * @link http://php.net/manual/en/function.array-count-values.php
   *
   * @return static <p>
   *                (Immutable)
   *                An associative Arrayy-object of values from input as
   *                keys and their count as value.
   *                </p>
   */
  public function countValues(): self
  {
    return new static(\array_count_values($this->array));
  }

  /**
   * Creates an Arrayy object.
   *
   * @param array $array
   *
   * @return static <p>(Immutable) Returns an new instance of the Arrayy object.</p>
   */
  public static function create($array = []): self
  {
    return new static($array);
  }

  /**
   * WARNING: Creates an Arrayy object by reference.
   *
   * @param array $array
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function createByReference(array &$array = []): self
  {
    $array = $this->fallbackForArray($array);

    $this->array = &$array;

    return $this;
  }

  /**
   * Create an new Arrayy object via JSON.
   *
   * @param string $json
   *
   * @return static <p>(Immutable) Returns an new instance of the Arrayy object.</p>
   */
  public static function createFromJson(string $json)
  {
    $array = UTF8::json_decode($json, true);

    return static::create($array);
  }

  /**
   * Create an new instance filled with values from an object that have implemented ArrayAccess.
   *
   * @param \ArrayAccess $object <p>Object that implements ArrayAccess</p>
   *
   * @return static <p>(Immutable) Returns an new instance of the Arrayy object.</p>
   */
  public static function createFromObject(\ArrayAccess $object)
  {
    $array = new static();
    foreach ($object as $key => $value) {
      /** @noinspection OffsetOperationsInspection */
      $array[$key] = $value;
    }

    return $array;
  }

  /**
   * Create an new instance filled with values from an object.
   *
   * @param object $object
   *
   * @return static <p>(Immutable) Returns an new instance of the Arrayy object.</p>
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
   * @return static <p>(Immutable) Returns an new instance of the Arrayy object.</p>
   */
  public static function createFromString(string $str, string $delimiter = null, string $regEx = null)
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
        function (&$val) {
          /** @noinspection ReferenceMismatchInspection */
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
   * @return static <p>(Immutable) Returns an new instance of the Arrayy object.</p>
   */
  public static function createWithRange($low, $high, int $step = 1)
  {
    return static::create(\range($low, $high, $step));
  }

  /**
   * Custom sort by index via "uksort".
   *
   * @link http://php.net/manual/en/function.uksort.php
   *
   * @param \callable $function
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   *
   * @throws \InvalidArgumentException
   */
  public function customSortKeys($function)
  {
    if (!\is_callable($function)) {
      throw new \InvalidArgumentException(
          'Passed function must be callable'
      );
    }

    \uksort($this->array, $function);

    return $this;
  }

  /**
   * Custom sort by value via "usort".
   *
   * @link http://php.net/manual/en/function.usort.php
   *
   * @param \callable $function
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   *
   * @throws \InvalidArgumentException
   */
  public function customSortValues($function)
  {
    if (!\is_callable($function)) {
      throw new \InvalidArgumentException(
          'Passed function must be callable'
      );
    }

    \usort($this->array, $function);

    return $this;
  }

  /**
   * Return values that are only in the current array.
   *
   * @param array $array
   *
   * @return static <p>(Immutable)</p>
   */
  public function diff(array $array = [])
  {
    $result = \array_diff($this->array, $array);

    return static::create($result);
  }

  /**
   * Return values that are only in the current multi-dimensional array.
   *
   * @param array      $array
   * @param null|array $helperVariableForRecursion <p>(only for internal usage)</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function diffRecursive(array $array = [], $helperVariableForRecursion = null)
  {
    $result = [];

    if (
        $helperVariableForRecursion !== null
        &&
        \is_array($helperVariableForRecursion)
    ) {
      $arrayForTheLoop = $helperVariableForRecursion;
    } else {
      $arrayForTheLoop = $this->array;
    }

    foreach ($arrayForTheLoop as $key => $value) {
      if (\array_key_exists($key, $array)) {
        if (\is_array($value)) {
          $recursiveDiff = $this->diffRecursive($array[$key], $value);
          if (!empty($recursiveDiff)) {
            $result[$key] = $recursiveDiff;
          }
        } elseif ($value != $array[$key]) {
          $result[$key] = $value;
        }
      } else {
        $result[$key] = $value;
      }
    }

    return static::create($result);
  }

  /**
   * Return values that are only in the new $array.
   *
   * @param array $array
   *
   * @return static <p>(Immutable)</p>
   */
  public function diffReverse(array $array = [])
  {
    $result = \array_diff($array, $this->array);

    return static::create($result);
  }

  /**
   * Divide an array into two arrays. One with keys and the other with values.
   *
   * @return static <p>(Immutable)</p>
   */
  public function divide()
  {
    return static::create(
        [
            $this->keys(),
            $this->values(),
        ]
    );
  }

  /**
   * Iterate over the current array and modify the array's value.
   *
   * @param \Closure $closure
   *
   * @return static <p>(Immutable)</p>
   */
  public function each(\Closure $closure)
  {
    $array = $this->array;

    foreach ($array as $key => $value) {
      $array[$key] = $closure($value, $key);
    }

    return static::create($array);
  }

  /**
   * Check if a value is in the current array using a closure.
   *
   * @param \Closure $closure
   *
   * @return bool <p>Returns true if the given value is found, false otherwise.</p>
   */
  public function exists(\Closure $closure): bool
  {
    $isExists = false;
    foreach ($this->array as $key => $value) {
      if ($closure($value, $key)) {
        $isExists = true;
        break;
      }
    }

    return $isExists;
  }

  /**
   * create a fallback for array
   *
   * 1. use the current array, if it's a array
   * 2. call "getArray()" on object, if there is a "Arrayy"-object
   * 3. fallback to empty array, if there is nothing
   * 4. call "createFromObject()" on object, if there is a "\ArrayAccess"-object
   * 5. call "__toArray()" on object, if the method exists
   * 6. cast a string or object with "__toString()" into an array
   * 7. throw a "InvalidArgumentException"-Exception
   *
   * @param $array
   *
   * @return array
   *
   * @throws \InvalidArgumentException
   */
  protected function fallbackForArray(&$array): array
  {
    if (\is_array($array)) {
      return $array;
    }

    if ($array instanceof self) {
      return $array->getArray();
    }

    if (!$array) {
      return [];
    }

    $isObject = \is_object($array);

    if ($isObject && $array instanceof \ArrayAccess) {
      /** @noinspection ReferenceMismatchInspection */
      return static::createFromObject($array)->getArray();
    }

    if ($isObject && $array instanceof \ArrayObject) {
      return $array->getArrayCopy();
    }

    if ($isObject && \method_exists($array, '__toArray')) {
      return (array)$array->__toArray();
    }

    /** @noinspection ReferenceMismatchInspection */
    if (
        \is_string($array)
        ||
        ($isObject && \method_exists($array, '__toString'))
    ) {
      return [(string)$array];
    }

    throw new \InvalidArgumentException(
        'Passed value should be a array'
    );
  }

  /**
   * Fill the array until "$num" with "$default" values.
   *
   * @param int   $num
   * @param mixed $default
   *
   * @return static <p>(Immutable)</p>
   */
  public function fillWithDefaults(int $num, $default = null)
  {
    if ($num < 0) {
      throw new \InvalidArgumentException('The $num parameter can only contain non-negative values.');
    }

    $tmpArray = $this->array;

    $count = \count($tmpArray);

    while ($count < $num) {
      $tmpArray[] = $default;
      $count++;
    }

    return static::create($tmpArray);
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
   *
   *  * @param int $flag [optional] <p>
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
   * @return static <p>(Immutable)</p>
   */
  public function filter($closure = null, int $flag = 0)
  {
    if (!$closure) {
      return $this->clean();
    }

    $array = \array_filter($this->array, $closure, $flag);

    return static::create($array);
  }

  /**
   * Filters an array of objects (or a numeric array of associative arrays) based on the value of a particular property
   * within that.
   *
   * @param string          $property
   * @param string|string[] $value
   * @param string          $comparisonOp
   *                            <p>
   *                            'eq' (equals),<br />
   *                            'gt' (greater),<br />
   *                            'gte' || 'ge' (greater or equals),<br />
   *                            'lt' (less),<br />
   *                            'lte' || 'le' (less or equals),<br />
   *                            'ne' (not equals),<br />
   *                            'contains',<br />
   *                            'notContains',<br />
   *                            'newer' (via strtotime),<br />
   *                            'older' (via strtotime),<br />
   *                            </p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function filterBy(string $property, $value, string $comparisonOp = null)
  {
    if (!$comparisonOp) {
      $comparisonOp = \is_array($value) ? 'contains' : 'eq';
    }

    $ops = [
        'eq'          => function ($item, $prop, $value) {
          return $item[$prop] === $value;
        },
        'gt'          => function ($item, $prop, $value) {
          return $item[$prop] > $value;
        },
        'ge'          => function ($item, $prop, $value) {
          return $item[$prop] >= $value;
        },
        'gte'         => function ($item, $prop, $value) {
          return $item[$prop] >= $value;
        },
        'lt'          => function ($item, $prop, $value) {
          return $item[$prop] < $value;
        },
        'le'          => function ($item, $prop, $value) {
          return $item[$prop] <= $value;
        },
        'lte'         => function ($item, $prop, $value) {
          return $item[$prop] <= $value;
        },
        'ne'          => function ($item, $prop, $value) {
          return $item[$prop] !== $value;
        },
        'contains'    => function ($item, $prop, $value) {
          return \in_array($item[$prop], (array)$value, true);
        },
        'notContains' => function ($item, $prop, $value) {
          return !\in_array($item[$prop], (array)$value, true);
        },
        'newer'       => function ($item, $prop, $value) {
          return \strtotime($item[$prop]) > \strtotime($value);
        },
        'older'       => function ($item, $prop, $value) {
          return \strtotime($item[$prop]) < \strtotime($value);
        },
    ];

    $result = \array_values(
        \array_filter(
            (array)$this->array,
            function ($item) use (
                $property,
                $value,
                $ops,
                $comparisonOp
            ) {
              $item = (array)$item;
              $itemArrayy = new Arrayy($item);
              $item[$property] = $itemArrayy->get($property, []);

              return $ops[$comparisonOp]($item, $property, $value);
            }
        )
    );

    return static::create($result);
  }

  /**
   * Find the first item in an array that passes the truth test,
   *  otherwise return false
   *
   * @param \Closure $closure
   *
   * @return mixed|false <p>Return false if we did not find the value.</p>
   */
  public function find(\Closure $closure)
  {
    foreach ($this->array as $key => $value) {
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
   * @return static <p>(Immutable)</p>
   */
  public function findBy(string $property, $value, string $comparisonOp = 'eq')
  {
    return $this->filterBy($property, $value, $comparisonOp);
  }

  /**
   * Get the first value from the current array.
   *
   * @return mixed <p>Return null if there wasn't a element.</p>
   */
  public function first()
  {
    $tmpArray = $this->array;
    $result = \array_shift($tmpArray);

    if ($result === null) {
      return null;
    }

    return $result;
  }

  /**
   * Get the first value(s) from the current array.
   *
   * @param int|null $number <p>How many values you will take?</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function firstsImmutable(int $number = null)
  {
    if ($number === null) {
      $arrayTmp = $this->array;
      $array = (array)\array_shift($arrayTmp);
    } else {
      $number = (int)$number;
      $arrayTmp = $this->array;
      $array = \array_splice($arrayTmp, 0, $number, true);
    }

    return static::create($array);
  }

  /**
   * Get the first value(s) from the current array.
   *
   * @param int|null $number <p>How many values you will take?</p>
   *
   * @return static <p>(Mutable)</p>
   */
  public function firstsMutable(int $number = null)
  {
    if ($number === null) {
      $this->array = (array)\array_shift($this->array);
    } else {
      $number = (int)$number;
      $this->array = \array_splice($this->array, 0, $number, true);
    }

    return $this;
  }

  /**
   * Exchanges all keys with their associated values in an array.
   *
   * @return static <p>(Immutable)</p>
   */
  public function flip()
  {
    $result = \array_flip($this->array);

    return static::create($result);
  }

  /**
   * Get a value from an array (optional using dot-notation).
   *
   * @param mixed $key       <p>The key to look for.</p>
   * @param mixed $fallback  <p>Value to fallback to.</p>
   * @param array $array     <p>The array to get from, if it's set to "null" we use the current array from the
   *                         class.</p>
   *
   * @return mixed
   */
  public function get($key, $fallback = null, array $array = null)
  {
    if ($array !== null) {
      $usedArray = $array;
    } else {
      $usedArray = $this->array;
    }

    if ($key === null) {
      return static::create($usedArray);
    }

    // php cast "bool"-index into "int"-index
    if ((bool)$key === $key) {
      $key = (int)$key;
    }

    if (\array_key_exists($key, $usedArray) === true) {
      if (\is_array($usedArray[$key])) {
        return static::create($usedArray[$key]);
      }

      return $usedArray[$key];
    }

    // Crawl through array, get key according to object or not
    foreach (\explode($this->pathSeparator, (string)$key) as $segment) {
      if (!isset($usedArray[$segment])) {
        return $fallback instanceof \Closure ? $fallback() : $fallback;
      }

      $usedArray = $usedArray[$segment];
    }

    if (\is_array($usedArray)) {
      return static::create($usedArray);
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
    \array_map(['self', 'internalGetArray'], $this->array);

    return $this->array;
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
   * @return static <p>(Immutable)</p>
   */
  public function getColumn($columnKey = null, $indexKey = null)
  {
    $result = \array_column($this->array, $columnKey, $indexKey);

    return static::create($result);
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
        $direction = SORT_DESC;
      } else {
        $direction = SORT_ASC;
      }
    }

    if (
        $direction !== SORT_DESC
        &&
        $direction !== SORT_ASC
    ) {
      $direction = SORT_ASC;
    }

    return $direction;
  }

  /**
   * alias: for "Arrayy->keys()"
   *
   * @see Arrayy::keys()
   *
   * @return static <p>(Immutable)</p>
   */
  public function getKeys()
  {
    return $this->keys();
  }

  /**
   * Get the current array from the "Arrayy"-object as object.
   *
   * @return \stdClass (object)
   */
  public function getObject(): \stdClass
  {
    return self::arrayToObject($this->getArray());
  }

  /**
   * alias: for "Arrayy->randomImmutable()"
   *
   * @see Arrayy::randomImmutable()
   *
   * @return static <p>(Immutable)</p>
   */
  public function getRandom()
  {
    return $this->randomImmutable();
  }

  /**
   * alias: for "Arrayy->randomKey()"
   *
   * @see Arrayy::randomKey()
   *
   * @return mixed <p>Get a key/index or null if there wasn't a key/index.</p>
   */
  public function getRandomKey()
  {
    return $this->randomKey();
  }

  /**
   * alias: for "Arrayy->randomKeys()"
   *
   * @see Arrayy::randomKeys()
   *
   * @param int $number
   *
   * @return static <p>(Immutable)</p>
   */
  public function getRandomKeys(int $number)
  {
    return $this->randomKeys($number);
  }

  /**
   * alias: for "Arrayy->randomValue()"
   *
   * @see Arrayy::randomValue()
   *
   * @return mixed <p>get a random value or null if there wasn't a value.</p>
   */
  public function getRandomValue()
  {
    return $this->randomValue();
  }

  /**
   * alias: for "Arrayy->randomValues()"
   *
   * @see Arrayy::randomValues()
   *
   * @param int $number
   *
   * @return static <p>(Immutable)</p>
   */
  public function getRandomValues(int $number)
  {
    return $this->randomValues($number);
  }

  /**
   * Group values from a array according to the results of a closure.
   *
   * @param \callable $grouper <p>A callable function name.</p>
   * @param bool      $saveKeys
   *
   * @return static <p>(Immutable)</p>
   */
  public function group($grouper, bool $saveKeys = false)
  {
    $array = (array)$this->array;
    $result = [];

    // Iterate over values, group by property/results from closure.
    foreach ($array as $key => $value) {

      $groupKey = \is_callable($grouper) ? $grouper($value, $key) : $this->get($grouper, null, $array);
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

    return static::create($result);
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
    // Generate unique string to use as marker.
    $unFound = \uniqid('arrayy', true);

    return $this->get($key, $unFound) !== $unFound;
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
    return $this->implode_recursive($glue, $this->array, false);
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
    return $this->implode_recursive($glue, $this->array, true);
  }

  /**
   * @param mixed               $glue
   * @param string|array|static $pieces
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
      $pieces_count = \count($pieces, COUNT_NORMAL);
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

    return (string)$pieces;
  }

  /**
   * @param mixed $needle   <p>
   *                        The searched value.
   *                        </p>
   *                        <p>
   *                        If needle is a string, the comparison is done
   *                        in a case-sensitive manner.
   *                        </p>
   * @param array $haystack <p>
   *                        The array.
   *                        </p>
   * @param bool  $strict   [optional] <p>
   *                        If the third parameter strict is set to true
   *                        then the in_array function will also check the
   *                        types of the
   *                        needle in the haystack.
   *                        </p>
   *
   * @return bool true if needle is found in the array, false otherwise.
   */
  protected function in_array_recursive($needle, array $haystack = null, $strict = true): bool
  {
    if ($haystack === null) {
      $haystack = $this->array;
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
   * Given a list and an iterate-function that returns
   * a key for each element in the list (or a property name),
   * returns an object with an index of each item.
   *
   * @param mixed $key
   *
   * @return static <p>(Immutable)</p>
   */
  public function indexBy($key)
  {
    $results = [];

    foreach ($this->array as $a) {
      if (\array_key_exists($key, $a) === true) {
        $results[$a[$key]] = $a;
      }
    }

    return static::create($results);
  }

  /**
   * alias: for "Arrayy->searchIndex()"
   *
   * @see Arrayy::searchIndex()
   *
   * @param mixed $value <p>The value to search for.</p>
   *
   * @return mixed
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
   * @return static <p>(Immutable)</p>
   */
  public function initial(int $to = 1)
  {
    return $this->firstsImmutable(\count($this->array, COUNT_NORMAL) - $to);
  }

  /**
   * @param mixed $value
   */
  protected function internalGetArray(&$value)
  {
    if ($value instanceof self) {

      $valueTmp = $value->getArray();
      if (\count($valueTmp, COUNT_NORMAL) === 0) {
        $value = [];
      } else {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $value = &$valueTmp;
      }

    } elseif ($value instanceof \JsonSerializable) {
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
    $path = \explode($this->pathSeparator, (string)$key);

    // Crawl though the keys
    while (\count($path, COUNT_NORMAL) > 1) {
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
   * @param mixed $key
   * @param mixed $value
   *
   * @return bool
   */
  protected function internalSet($key, $value): bool
  {
    if ($key === null) {
      return false;
    }

    // init
    $array =& $this->array;
    $path = \explode($this->pathSeparator, (string)$key);

    // Crawl through the keys
    while (\count($path, COUNT_NORMAL) > 1) {
      $key = \array_shift($path);

      // If the key doesn't exist at this depth, we will just create an empty array
      // to hold the next value, allowing us to create the arrays to hold final
      // values at the correct depth. Then we'll keep digging into the array.
      if (!isset($array[$key]) || !\is_array($array[$key])) {
        $array[$key] = static::create([]);
      }

      $array =& $array[$key];
    }

    $array[\array_shift($path)] = $value;

    return true;
  }

  /**
   * Return an array with all elements found in input array.
   *
   * @param array $search
   *
   * @return static <p>(Immutable)</p>
   */
  public function intersection(array $search)
  {
    return static::create(\array_values(\array_intersect($this->array, $search)));
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
    return \count($this->intersection($search)->array, COUNT_NORMAL) > 0;
  }

  /**
   * Invoke a function on all of an array's values.
   *
   * @param mixed $callable
   * @param mixed $arguments
   *
   * @return static <p>(Immutable)</p>
   */
  public function invoke($callable, $arguments = [])
  {
    // If one argument given for each iteration, create an array for it.
    if (!\is_array($arguments)) {
      $arguments = StaticArrayy::repeat(
          $arguments,
          \count($this->array, COUNT_NORMAL)
      )->getArray();
    }

    // If the callable has arguments, pass them.
    if ($arguments) {
      $array = \array_map($callable, $this->array, $arguments);
    } else {
      $array = \array_map($callable, $this->array);
    }

    return static::create($array);
  }

  /**
   * Check whether array is associative or not.
   *
   * @param bool $recursive
   *
   * @return bool <p>Returns true if associative, false otherwise.</p>
   */
  public function isAssoc(bool $recursive = false): bool
  {
    if ($this->isEmpty()) {
      return false;
    }

    foreach ($this->keys($recursive)->getArray() as $key) {
      if (!\is_string($key)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Check whether the array is empty or not.
   *
   * @return bool <p>Returns true if empty, false otherwise.</p>
   */
  public function isEmpty(): bool
  {
    return !$this->array;
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
    return ($this->array === $array);
  }

  /**
   * Check if the current array is a multi-array.
   *
   * @return bool
   */
  public function isMultiArray(): bool
  {
    return !(
        \count($this->array, COUNT_NORMAL)
        ===
        \count($this->array, COUNT_RECURSIVE)
    );
  }

  /**
   * Check whether array is numeric or not.
   *
   * @return bool <p>Returns true if numeric, false otherwise.</p>
   */
  public function isNumeric(): bool
  {
    if ($this->isEmpty()) {
      return false;
    }

    foreach ($this->keys() as $key) {
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
      return $this->array_keys_recursive($this->array)
             ===
             \range(0, \count($this->array, COUNT_RECURSIVE) - 1);
    }

    // non recursive

    return \array_keys($this->array)
           ===
           \range(0, \count($this->array, COUNT_NORMAL) - 1);
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
   * @return static <p>(Immutable) An array of all the keys in input.</p>
   */
  public function keys(bool $recursive = false, $search_value = null, bool $strict = true)
  {

    // recursive

    if ($recursive === true) {
      if ($search_value === null) {
        $array = $this->array_keys_recursive($this->array);
      } else {
        $array = $this->array_keys_recursive($this->array, $search_value, $strict);
      }

      return static::create($array);
    }

    // non recursive

    if ($search_value === null) {
      $array = \array_keys($this->array);
    } else {
      $array = \array_keys($this->array, $search_value, $strict);
    }

    return static::create($array);
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
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function krsort(int $sort_flags = 0)
  {
    krsort($this->array, $sort_flags);

    return $this;
  }

  /**
   * Get the last value from the current array.
   *
   * @return mixed <p>Return null if there wasn't a element.</p>
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
   * @return static <p>(Immutable)</p>
   */
  public function lastsImmutable(int $number = null)
  {
    if ($this->isEmpty()) {
      return static::create();
    }

    if ($number === null) {
      $poppedValue = $this->pop();

      if ($poppedValue === null) {
        $poppedValue = [$poppedValue];
      } else {
        $poppedValue = (array)$poppedValue;
      }

      $arrayy = static::create($poppedValue);
    } else {
      $number = (int)$number;
      $arrayy = $this->rest(-$number);
    }

    return $arrayy;
  }

  /**
   * Get the last value(s) from the current array.
   *
   * @param int|null $number
   *
   * @return static <p>(Mutable)</p>
   */
  public function lastsMutable(int $number = null)
  {
    if ($this->isEmpty()) {
      return $this;
    }

    if ($number === null) {
      $poppedValue = $this->pop();

      if ($poppedValue === null) {
        $poppedValue = [$poppedValue];
      } else {
        $poppedValue = (array)$poppedValue;
      }

      $this->array = static::create($poppedValue)->array;
    } else {
      $number = (int)$number;
      $this->array = $this->rest(-$number)->array;
    }

    return $this;
  }

  /**
   * Count the values from the current array.
   *
   * alias: for "Arrayy->count()"
   *
   * @see Arrayy::count()
   *
   * @param int $mode
   *
   * @return int
   */
  public function length(int $mode = COUNT_NORMAL): int
  {
    return $this->count($mode);
  }

  /**
   * Apply the given function to the every element of the array,
   * collecting the results.
   *
   * @param \callable $callable
   *
   * @return static <p>(Immutable) Arrayy object with modified elements.</p>
   */
  public function map($callable)
  {
    $result = \array_map($callable, $this->array);

    return static::create($result);
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
    if (\count($this->array, COUNT_NORMAL) === 0) {
      return false;
    }

    // init
    $array = $this->array;

    foreach ($array as $key => $value) {
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
    if (\count($this->array, COUNT_NORMAL) === 0) {
      return false;
    }

    // init
    $array = $this->array;

    foreach ($array as $key => $value) {
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
    if (\count($this->array, COUNT_NORMAL) === 0) {
      return false;
    }

    return max($this->array);
  }

  /**
   * Merge the new $array into the current array.
   *
   * - keep key,value from the current array, also if the index is in the new $array
   *
   * @param array $array
   * @param bool  $recursive
   *
   * @return static <p>(Immutable)</p>
   */
  public function mergeAppendKeepIndex(array $array = [], bool $recursive = false)
  {
    if (true === $recursive) {
      $result = \array_replace_recursive($this->array, $array);
    } else {
      $result = \array_replace($this->array, $array);
    }

    return static::create($result);
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
   * @return static <p>(Immutable)</p>
   */
  public function mergeAppendNewIndex(array $array = [], bool $recursive = false)
  {
    if (true === $recursive) {
      $result = \array_merge_recursive($this->array, $array);
    } else {
      $result = \array_merge($this->array, $array);
    }

    return static::create($result);
  }

  /**
   * Merge the the current array into the $array.
   *
   * - use key,value from the new $array, also if the index is in the current array
   *
   * @param array $array
   * @param bool  $recursive
   *
   * @return static <p>(Immutable)</p>
   */
  public function mergePrependKeepIndex(array $array = [], bool $recursive = false)
  {
    if (true === $recursive) {
      $result = \array_replace_recursive($array, $this->array);
    } else {
      $result = \array_replace($array, $this->array);
    }

    return static::create($result);
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
   * @return static <p>(Immutable)</p>
   */
  public function mergePrependNewIndex(array $array = [], bool $recursive = false)
  {
    if (true === $recursive) {
      $result = \array_merge_recursive($array, $this->array);
    } else {
      $result = \array_merge($array, $this->array);
    }

    return static::create($result);
  }

  /**
   * Get the min value from an array.
   *
   * @return mixed
   */
  public function min()
  {
    if (\count($this->array, COUNT_NORMAL) === 0) {
      return false;
    }

    return min($this->array);
  }

  /**
   * Move an array element to a new index.
   *
   * cherry-picked from: http://stackoverflow.com/questions/12624153/move-an-array-element-to-a-new-index-in-php
   *
   * @param int|string $from
   * @param int|string $to
   *
   * @return static <p>(Immutable)</p>
   */
  public function moveElement($from, $to)
  {
    $array = $this->array;

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
        if ($i == $to) {
          $output[$from] = $itemToMove;
        }
        $output[$key] = $item;
        $i++;
      }
    } else {
      $output = [];
    }

    return static::create($output);
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
   * Get a subset of the items from the given array.
   *
   * @param mixed[] $keys
   *
   * @return static <p>(Immutable)</p>
   */
  public function only(array $keys)
  {
    $array = $this->array;

    return static::create(\array_intersect_key($array, \array_flip($keys)));
  }

  /**
   * Pad array to the specified size with a given value.
   *
   * @param int   $size  <p>Size of the result array.</p>
   * @param mixed $value <p>Empty value by default.</p>
   *
   * @return static <p>(Immutable) Arrayy object padded to $size with $value.</p>
   */
  public function pad(int $size, $value)
  {
    $result = \array_pad($this->array, $size, $value);

    return static::create($result);
  }

  /**
   * Pop a specified value off the end of the current array.
   *
   * @return mixed <p>(Mutable) The popped element from the current array.</p>
   */
  public function pop()
  {
    return \array_pop($this->array);
  }

  /**
   * Prepend a (key) + value to the current array.
   *
   * @param mixed $value
   * @param mixed $key
   *
   * @return static <p>(Mutable) Return this Arrayy object, with the prepended value.</p>
   */
  public function prepend($value, $key = null)
  {
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
   * @return static <p>(Immutable) Return an Arrayy object, with the prepended keys.</p>
   */
  public function prependToEachKey($suffix)
  {
    $result = [];
    foreach ($this->array as $key => $item) {
      if ($item instanceof self) {
        $result[$key] = $item->prependToEachKey($suffix);
      } elseif (\is_array($item)) {
        $result[$key] = self::create($item)->prependToEachKey($suffix)->toArray();
      } else {
        $result[$key . $suffix] = $item;
      }

    }

    return self::create($result);
  }

  /**
   * Add a suffix to each value.
   *
   * @param mixed $suffix
   *
   * @return static <p>(Immutable) Return an Arrayy object, with the prepended values.</p>
   */
  public function prependToEachValue($suffix)
  {
    $result = [];
    foreach ($this->array as $key => $item) {
      if ($item instanceof self) {
        $result[$key] = $item->prependToEachValue($suffix);
      } elseif (\is_array($item)) {
        $result[$key] = self::create($item)->prependToEachValue($suffix)->toArray();
      } elseif (\is_object($item)) {
        $result[$key] = $item;
      } else {
        $result[$key] = $item . $suffix;
      }
    }

    return self::create($result);
  }

  /**
   * Push one or more values onto the end of array at once.
   *
   * @return static <p>(Mutable) Return this Arrayy object, with pushed elements to the end of array.</p>
   */
  public function push(/* variadic arguments allowed */)
  {
    if (\func_num_args()) {
      $args = \array_merge([&$this->array], \func_get_args());
      \array_push(...$args);
    }

    return $this;
  }

  /**
   * Get a random value from the current array.
   *
   * @param null|int $number <p>How many values you will take?</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function randomImmutable(int $number = null)
  {
    if (\count($this->array, COUNT_NORMAL) === 0) {
      return static::create();
    }

    if ($number === null) {
      $arrayRandValue = [$this->array[\array_rand($this->array)]];

      return static::create($arrayRandValue);
    }

    $arrayTmp = $this->array;
    \shuffle($arrayTmp);

    return static::create($arrayTmp)->firstsImmutable($number);
  }

  /**
   * Pick a random key/index from the keys of this array.
   *
   * @return mixed <p>Get a key/index or null if there wasn't a key/index.</p>
   *
   * @throws \RangeException If array is empty
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
   * @return static <p>(Immutable)</p>
   *
   * @throws \RangeException If array is empty
   */
  public function randomKeys(int $number)
  {
    $count = \count($this->array, COUNT_NORMAL);

    if ($number === 0 || $number > $count) {
      throw new \RangeException(
          \sprintf(
              'Number of requested keys (%s) must be equal or lower than number of elements in this array (%s)',
              $number,
              $count
          )
      );
    }

    $result = (array)\array_rand($this->array, $number);

    return static::create($result);
  }

  /**
   * Get a random value from the current array.
   *
   * @param null|int $number <p>How many values you will take?</p>
   *
   * @return static <p>(Mutable)</p>
   */
  public function randomMutable(int $number = null)
  {
    if (\count($this->array, COUNT_NORMAL) === 0) {
      return static::create();
    }

    if ($number === null) {
      $arrayRandValue = [$this->array[\array_rand($this->array)]];
      $this->array = $arrayRandValue;

      return $this;
    }

    \shuffle($this->array);

    return $this->firstsMutable($number);
  }

  /**
   * Pick a random value from the values of this array.
   *
   * @return mixed <p>Get a random value or null if there wasn't a value.</p>
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
   * @return static <p>(Mutable)</p>
   */
  public function randomValues(int $number)
  {
    return $this->randomMutable($number);
  }

  /**
   * Get a random value from an array, with the ability to skew the results.
   *
   * Example: randomWeighted(['foo' => 1, 'bar' => 2]) has a 66% chance of returning bar.
   *
   * @param array    $array
   * @param null|int $number <p>How many values you will take?</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function randomWeighted(array $array, int $number = null)
  {
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
   * @return static <p>(Immutable)</p>
   */
  public function reduce($callable, array $init = [])
  {
    $result = \array_reduce($this->array, $callable, $init);

    if ($result === null) {
      $this->array = [];
    } else {
      $this->array = (array)$result;
    }

    return static::create($this->array);
  }

  /**
   * Create a numerically re-indexed Arrayy object.
   *
   * @return static <p>(Mutable) Return this Arrayy object, with re-indexed array-elements.</p>
   */
  public function reindex()
  {
    $this->array = \array_values($this->array);

    return $this;
  }

  /**
   * Return all items that fail the truth test.
   *
   * @param \Closure $closure
   *
   * @return static <p>(Immutable)</p>
   */
  public function reject(\Closure $closure)
  {
    $filtered = [];

    foreach ($this->array as $key => $value) {
      if (!$closure($value, $key)) {
        $filtered[$key] = $value;
      }
    }

    return static::create($filtered);
  }

  /**
   * Remove a value from the current array (optional using dot-notation).
   *
   * @param mixed $key
   *
   * @return static <p>(Immutable)</p>
   */
  public function remove($key)
  {
    // recursive call
    if (\is_array($key)) {
      foreach ($key as $k) {
        $this->internalRemove($k);
      }

      return static::create($this->array);
    }

    $this->internalRemove($key);

    return static::create($this->array);
  }

  /**
   * Remove the first value from the current array.
   *
   * @return static <p>(Immutable)</p>
   */
  public function removeFirst()
  {
    $tmpArray = $this->array;
    \array_shift($tmpArray);

    return static::create($tmpArray);
  }

  /**
   * Remove the last value from the current array.
   *
   * @return static <p>(Immutable)</p>
   */
  public function removeLast()
  {
    $tmpArray = $this->array;
    \array_pop($tmpArray);

    return static::create($tmpArray);
  }

  /**
   * Removes a particular value from an array (numeric or associative).
   *
   * @param mixed $value
   *
   * @return static <p>(Immutable)</p>
   */
  public function removeValue($value)
  {
    $isNumericArray = true;
    foreach ($this->array as $key => $item) {
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

    return static::create($this->array);
  }

  /**
   * Generate array of repeated arrays.
   *
   * @param int $times <p>How many times has to be repeated.</p>
   *
   * @return Arrayy
   */
  public function repeat($times): self
  {
    if ($times === 0) {
      return new static();
    }

    return static::create(\array_fill(0, (int)$times, $this->array));
  }

  /**
   * Replace a key with a new key/value pair.
   *
   * @param mixed $replace
   * @param mixed $key
   * @param mixed $value
   *
   * @return static <p>(Immutable)</p>
   */
  public function replace($replace, $key, $value)
  {
    $this->remove($replace);

    return $this->set($key, $value);
  }

  /**
   * Create an array using the current array as values and the other array as keys.
   *
   * @param array $keys <p>An array of keys.</p>
   *
   * @return static <p>(Immutable) Arrayy object with keys from the other array.</p>
   */
  public function replaceAllKeys(array $keys)
  {
    $result = \array_combine($keys, $this->array);

    return static::create($result);
  }

  /**
   * Create an array using the current array as keys and the other array as values.
   *
   * @param array $array <p>An array o values.</p>
   *
   * @return static <p>(Immutable) Arrayy object with values from the other array.</p>
   */
  public function replaceAllValues(array $array)
  {
    $result = \array_combine($this->array, $array);

    return static::create($result);
  }

  /**
   * Replace the keys in an array with another set.
   *
   * @param array $keys <p>An array of keys matching the array's size</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function replaceKeys(array $keys)
  {
    $values = \array_values($this->array);
    $result = \array_combine($keys, $values);

    return static::create($result);
  }

  /**
   * Replace the first matched value in an array.
   *
   * @param mixed $search      <p>The value to replace.</p>
   * @param mixed $replacement <p>The value to replace.</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function replaceOneValue($search, $replacement = '')
  {
    $array = $this->array;
    $key = \array_search($search, $array, true);

    if ($key !== false) {
      $array[$key] = $replacement;
    }

    return static::create($array);
  }

  /**
   * Replace values in the current array.
   *
   * @param mixed $search      <p>The value to replace.</p>
   * @param mixed $replacement <p>What to replace it with.</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function replaceValues($search, $replacement = '')
  {
    $array = $this->each(
        function ($value) use ($search, $replacement) {
          return UTF8::str_replace($search, $replacement, $value);
        }
    );

    return $array;
  }

  /**
   * Get the last elements from index $from until the end of this array.
   *
   * @param int $from
   *
   * @return static <p>(Immutable)</p>
   */
  public function rest(int $from = 1)
  {
    $tmpArray = $this->array;

    return static::create(\array_splice($tmpArray, $from));
  }

  /**
   * Return the array in the reverse order.
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function reverse()
  {
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
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function rsort(int $sort_flags = 0)
  {
    \rsort($this->array, $sort_flags);

    return $this;
  }

  /**
   * Search for the first index of the current array via $value.
   *
   * @param mixed $value
   *
   * @return int|float|string
   */
  public function searchIndex($value)
  {
    return \array_search($value, $this->array, true);
  }

  /**
   * Search for the value of the current array via $index.
   *
   * @param mixed $index
   *
   * @return static <p>(Immutable) Will return a empty Arrayy if the value wasn't found.</p>
   */
  public function searchValue($index)
  {
    // init
    $return = [];

    if ($this->isEmpty()) {
      return static::create();
    }

    // php cast "bool"-index into "int"-index
    if ((bool)$index === $index) {
      $index = (int)$index;
    }

    if (\array_key_exists($index, $this->array) === true) {
      $return = [$this->array[$index]];
    }


    return static::create($return);
  }

  /**
   * Set a value for the current array (optional using dot-notation).
   *
   * @param mixed $key   <p>The key to set.</p>
   * @param mixed $value <p>Its value.</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function set($key, $value)
  {
    $this->internalSet($key, $value);

    return static::create($this->array);
  }

  /**
   * Get a value from a array and set it if it was not.
   *
   * WARNING: this method only set the value, if the $key is not already set
   *
   * @param mixed $key      <p>The key</p>
   * @param mixed $fallback <p>The default value to set if it isn't.</p>
   *
   * @return mixed <p>(Mutable)</p>
   */
  public function setAndGet($key, $fallback = null)
  {
    // If the key doesn't exist, set it.
    if (!$this->has($key)) {
      $this->array = $this->set($key, $fallback)->getArray();
    }

    return $this->get($key);
  }

  /**
   * Shifts a specified value off the beginning of array.
   *
   * @return mixed <p>(Mutable) A shifted element from the current array.</p>
   */
  public function shift()
  {
    return \array_shift($this->array);
  }

  /**
   * Shuffle the current array.
   *
   * @param bool  $secure <p>using a CSPRNG | @link https://paragonie.com/b/JvICXzh_jhLyt4y3</p>
   * @param array $array  [optional]
   *
   * @return static <p>(Immutable)</p>
   */
  public function shuffle(bool $secure = false, array $array = null)
  {
    if ($array === null) {
      $array = $this->array;
    }

    if ($secure !== true) {
      \shuffle($array);
    } else {
      $size = \count($array, COUNT_NORMAL);
      $keys = \array_keys($array);
      for ($i = $size - 1; $i > 0; --$i) {
        try {
          $r = \random_int(0, $i);
        } catch (\Exception $e) {
          $r = \mt_rand();
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

    return static::create($array);
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
  public function size(int $mode = COUNT_NORMAL): int
  {
    return $this->count($mode);
  }

  /**
   * Counts all elements in an array, or something in an object.
   * <p>For objects, if you have SPL installed, you can hook into count() by implementing interface {@see Countable}.
   * The interface has exactly one method, {@see Countable::count()}, which returns the return value for the count()
   * function. Please see the {@see Array} section of the manual for a detailed explanation of how arrays are
   * implemented and used in PHP.
   *
   * @return int the number of elements in var, which is
   * typically an array, since anything else will have one
   * element.
   * </p>
   * <p>
   * If var is not an array or an object with
   * implemented Countable interface,
   * 1 will be returned.
   * There is one exception, if var is &null;,
   * 0 will be returned.
   * </p>
   * <p>
   * Caution: count may return 0 for a variable that isn't set,
   * but it may also return 0 for a variable that has been initialized with an
   * empty array. Use isset to test if a variable is set.
   *
   * @return int
   */
  public function sizeRecursive(): int
  {
    return \count($this->array, COUNT_RECURSIVE);
  }

  /**
   * Extract a slice of the array.
   *
   * @param int      $offset       <p>Slice begin index.</p>
   * @param int|null $length       <p>Length of the slice.</p>
   * @param bool     $preserveKeys <p>Whether array keys are preserved or no.</p>
   *
   * @return static <p>A slice of the original array with length $length.</p>
   */
  public function slice(int $offset, int $length = null, bool $preserveKeys = false)
  {
    $result = \array_slice($this->array, $offset, $length, $preserveKeys);

    return static::create($result);
  }

  /**
   * Sort the current array and optional you can keep the keys.
   *
   * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
   * @param int        $strategy  <p>sort_flags => use e.g.: <strong>SORT_REGULAR</strong> (default) or
   *                              <strong>SORT_NATURAL</strong></p>
   * @param bool       $keepKeys
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function sort($direction = SORT_ASC, int $strategy = SORT_REGULAR, bool $keepKeys = false)
  {
    $this->sorting($this->array, $direction, $strategy, $keepKeys);

    return $this;
  }

  /**
   * Sort the current array by key.
   *
   * @link http://php.net/manual/en/function.ksort.php
   * @link http://php.net/manual/en/function.krsort.php
   *
   * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
   * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
   *                              <strong>SORT_NATURAL</strong></p>
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  public function sortKeys($direction = SORT_ASC, int $strategy = SORT_REGULAR)
  {
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
   * @return static <p>(Mutable)</p>
   */
  public function sortValueKeepIndex($direction = SORT_ASC, int $strategy = SORT_REGULAR)
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
   * @return static <p>(Mutable)</p>
   */
  public function sortValueNewIndex($direction = SORT_ASC, int $strategy = SORT_REGULAR)
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
   * @param string|int     $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
   * @param int            $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
   *                                  <strong>SORT_NATURAL</strong></p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function sorter($sorter = null, $direction = SORT_ASC, int $strategy = SORT_REGULAR)
  {
    $array = (array)$this->array;
    $direction = $this->getDirection($direction);

    // Transform all values into their results.
    if ($sorter) {
      $arrayy = static::create($array);

      $that = $this;
      $results = $arrayy->each(
          function ($value) use ($sorter, $that) {
            return \is_callable($sorter) ? $sorter($value) : $that->get($sorter, null, $value);
          }
      );

      $results = $results->getArray();
    } else {
      $results = $array;
    }

    // Sort by the results and replace by original values
    \array_multisort($results, $direction, $strategy, $array);

    return static::create($array);
  }

  /**
   * sorting keys
   *
   * @param array      $elements
   * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
   * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
   *                              <strong>SORT_NATURAL</strong></p>
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  protected function sorterKeys(array &$elements, $direction = SORT_ASC, int $strategy = SORT_REGULAR)
  {
    $direction = $this->getDirection($direction);

    switch ($direction) {
      case 'desc':
      case SORT_DESC:
        \krsort($elements, $strategy);
        break;
      case 'asc':
      case SORT_ASC:
      default:
        \ksort($elements, $strategy);
    }

    return $this;
  }

  /**
   * @param array      &$elements
   * @param int|string $direction <p>use <strong>SORT_ASC</strong> (default) or <strong>SORT_DESC</strong></p>
   * @param int        $strategy  <p>use e.g.: <strong>SORT_REGULAR</strong> (default) or
   *                              <strong>SORT_NATURAL</strong></p>
   * @param bool       $keepKeys
   *
   * @return static <p>(Mutable) Return this Arrayy object.</p>
   */
  protected function sorting(array &$elements, $direction = SORT_ASC, int $strategy = SORT_REGULAR, bool $keepKeys = false)
  {
    $direction = $this->getDirection($direction);

    if (!$strategy) {
      $strategy = SORT_REGULAR;
    }

    switch ($direction) {
      case 'desc':
      case SORT_DESC:
        if ($keepKeys) {
          arsort($elements, $strategy);
        } else {
          \rsort($elements, $strategy);
        }
        break;
      case 'asc':
      case SORT_ASC:
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
   * Split an array in the given amount of pieces.
   *
   * @param int  $numberOfPieces
   * @param bool $keepKeys
   *
   * @return static <p>(Immutable)</p>
   */
  public function split(int $numberOfPieces = 2, bool $keepKeys = false)
  {
    $arrayCount = \count($this->array, COUNT_NORMAL);

    if ($arrayCount === 0) {
      $result = [];
    } else {
      $splitSize = (int)\ceil($arrayCount / $numberOfPieces);
      $result = \array_chunk($this->array, $splitSize, $keepKeys);
    }

    return static::create($result);
  }

  /**
   * Stripe all empty items.
   *
   * @return static <p>(Immutable)</p>
   */
  public function stripEmpty()
  {
    return $this->filter(
        function ($item) {
          if ($item === null) {
            return false;
          }

          return (bool)\trim((string)$item);
        }
    );
  }

  /**
   * Swap two values between positions by key.
   *
   * @param string|int $swapA <p>a key in the array</p>
   * @param string|int $swapB <p>a key in the array</p>
   *
   * @return static <p>(Immutable)</p>
   */
  public function swap($swapA, $swapB)
  {
    $array = $this->array;

    list($array[$swapA], $array[$swapB]) = [$array[$swapB], $array[$swapA]];

    return static::create($array);
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
    return UTF8::json_encode($this->array, $options, $depth);
  }

  /**
   * Implodes array to a string with specified separator.
   *
   * @param string $separator [optional] <p>The element's separator.</p>
   *
   * @return string <p>The string representation of array, separated by ",".</p>
   */
  public function toString(string $separator = ','): string
  {
    return $this->implode($separator);
  }

  /**
   * Return a duplicate free copy of the current array.
   *
   * @return static <p>(Mutable)</p>
   */
  public function unique()
  {
    // INFO: \array_unique() can't handle e.g. "stdClass"-values in an array

    $this->array = \array_reduce(
        $this->array,
        function ($resultArray, $value) {
          if (!\in_array($value, $resultArray, true)) {
            $resultArray[] = $value;
          }

          return $resultArray;
        },
        []
    );

    if ($this->array === null) {
      $this->array = [];
    } else {
      $this->array = (array)$this->array;
    }

    return $this;
  }

  /**
   * Return a duplicate free copy of the current array. (with the old keys)
   *
   * @return static <p>(Mutable)</p>
   */
  public function uniqueKeepIndex()
  {
    // INFO: \array_unique() can't handle e.g. "stdClass"-values in an array

    // init
    $array = $this->array;

    $this->array = \array_reduce(
        \array_keys($array),
        function ($resultArray, $key) use ($array) {
          if (!\in_array($array[$key], $resultArray, true)) {
            $resultArray[$key] = $array[$key];
          }

          return $resultArray;
        },
        []
    );

    if ($this->array === null) {
      $this->array = [];
    } else {
      $this->array = (array)$this->array;
    }

    return $this;
  }

  /**
   * alias: for "Arrayy->unique()"
   *
   * @see Arrayy::unique()
   *
   * @return static <p>(Mutable) Return this Arrayy object, with the appended values.</p>
   */
  public function uniqueNewIndex()
  {
    return $this->unique();
  }

  /**
   * Prepends one or more values to the beginning of array at once.
   *
   * @return static <p>(Mutable) Return this Arrayy object, with prepended elements to the beginning of array.</p>
   */
  public function unshift(/* variadic arguments allowed */)
  {
    if (\func_num_args()) {
      $args = \array_merge([&$this->array], \func_get_args());
      \array_unshift(...$args);
    }

    return $this;
  }

  /**
   * Get all values from a array.
   *
   * @return static <p>(Immutable)</p>
   */
  public function values()
  {
    return static::create(\array_values((array)$this->array));
  }

  /**
   * Apply the given function to every element in the array, discarding the results.
   *
   * @param \callable $callable
   * @param bool      $recursive <p>Whether array will be walked recursively or no</p>
   *
   * @return static <p>(Mutable) Return this Arrayy object, with modified elements.</p>
   */
  public function walk($callable, bool $recursive = false)
  {
    if (true === $recursive) {
      \array_walk_recursive($this->array, $callable);
    } else {
      \array_walk($this->array, $callable);
    }

    return $this;
  }
}
