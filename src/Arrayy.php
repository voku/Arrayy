<?php

namespace Arrayy;

use ArrayAccess;
use Closure;
use voku\helper\UTF8;

/**
 * Methods to manage arrays.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Arrayy extends \ArrayObject implements \Countable, \IteratorAggregate, \ArrayAccess, \Serializable
{
  /**
   * @var array
   */
  protected $array = array();

  /**
   * Initializes
   *
   * @param array $array
   */
  public function __construct($array = array())
  {
    $array = $this->fallbackForArray($array);

    $this->array = $array;
  }

  /**
   * create a fallback for array
   *
   * 1. fallback to empty array, if there is nothing
   * 2. cast a String or Object with "__toString" into an array
   * 3. call "__toArray" on Object, if the method exists
   * 4. throw a "InvalidArgumentException"-Exception
   *
   * @param $array
   *
   * @return array
   */
  protected function fallbackForArray(&$array)
  {
    if (is_array($array)) {
      return $array;
    }

    if ($array instanceof self) {
      return $array->getArray();
    }

    if (!$array) {
      return array();
    }

    if (
        is_string($array)
        ||
        (is_object($array) && method_exists($array, '__toString'))
    ) {
      return (array)$array;
    }

    if (is_object($array) && method_exists($array, '__toArray')) {
      return (array)$array->__toArray();
    }

    throw new \InvalidArgumentException(
        'Passed value must be a array'
    );
  }

  /**
   * Get the current array from the "Arrayy"-object
   *
   * @return array
   */
  public function getArray()
  {
    return $this->array;
  }

  /**
   * Create a new Arrayy object via string.
   *
   * @param string      $str       The input string.
   * @param string|null $delimiter The boundary string.
   * @param string|null $regEx     Use the $delimiter or the $regEx, so if $pattern is null, $delimiter will be used.
   *
   * @return Arrayy Returns created instance
   */
  public static function createFromString($str, $delimiter, $regEx = null)
  {
    if ($regEx) {
      preg_match_all($regEx, $str, $array);

      if (count($array) > 0) {
        $array = $array[0];
      }

    } else {
      $array = explode($delimiter, $str);
    }

    // trim all string in the array
    array_walk(
        $array,
        function (&$val) {
          if (is_string($val)) {
            $val = trim($val);
          }
        }
    );

    return static::create($array);
  }

  /**
   * Creates a Arrayy object.
   *
   * @param array $array
   *
   * @return Arrayy Returns created instance
   */
  public static function create($array = array())
  {
    return new static($array);
  }

  /**
   * create a new Arrayy object via JSON,
   *
   * @param string $json
   *
   * @return Arrayy Returns created instance
   */
  public static function createFromJson($json)
  {
    $array = UTF8::json_decode($json, true);

    return static::create($array);
  }

  /**
   * Create a new instance filled with values from an object implementing ArrayAccess.
   *
   * @param ArrayAccess $elements Object that implements ArrayAccess
   *
   * @return Arrayy Returns created instance
   */
  public static function createFromObject(ArrayAccess $elements)
  {
    $array = new static();
    foreach ($elements as $key => $value) {
      /** @noinspection OffsetOperationsInspection */
      $array[$key] = $value;
    }

    return $array;
  }

  /**
   * Create a new instance containing a range of elements.
   *
   * @param mixed $low  First value of the sequence
   * @param mixed $high The sequence is ended upon reaching the end value
   * @param int   $step Used as the increment between elements in the sequence
   *
   * @return Arrayy The created array
   */
  public static function createWithRange($low, $high, $step = 1)
  {
    return static::create(range($low, $high, $step));
  }

  /**
   * alias: for "Arrayy->random()"
   *
   * @return Arrayy
   */
  public function getRandom()
  {
    return $this->random();
  }

  /**
   * Get a random value from the current array.
   *
   * @param null|int $number how many values you will take?
   *
   * @return Arrayy
   */
  public function random($number = null)
  {
    if ($this->count() === 0) {
      return static::create();
    }

    if ($number === null) {
      $arrayRandValue = (array)$this->array[array_rand($this->array)];

      return static::create($arrayRandValue);
    }

    shuffle($this->array);

    return $this->first($number);
  }

  /**
   * Count the values from the current array.
   *
   * INFO: only a alias for "$arrayy->size()"
   *
   * @return int
   */
  public function count()
  {
    return $this->size();
  }

  /**
   * Get the size of an array.
   *
   * @return int
   */
  public function size()
  {
    return count($this->array);
  }

  /**
   * Get the first value(s) from the current array.
   *
   * @param int|null $number how many values you will take?
   *
   * @return Arrayy
   */
  public function first($number = null)
  {
    if ($number === null) {
      $array = (array)array_shift($this->array);
    } else {
      $number = (int)$number;
      $array = array_splice($this->array, 0, $number, true);
    }

    return static::create($array);
  }

  /**
   * Append a value to an array.
   *
   * @param mixed $value
   *
   * @return Arrayy
   */
  public function append($value)
  {
    $this->array[] = $value;

    return static::create($this->array);
  }

  /**
   * @return mixed
   */
  public function serialize()
  {
    return serialize($this->array);
  }

  /**
   * @param string $array
   */
  public function unserialize($array)
  {
    $this->array = unserialize($array);
  }

  /**
   * Assigns a value to the specified offset.
   *
   * @param mixed $offset
   * @param mixed $value
   */
  public function offsetSet($offset, $value)
  {
    if (null === $offset) {
      $this->array[] = $value;
    } else {
      $this->array[$offset] = $value;
    }
  }

  /**
   * alias: for "Arrayy->randomValue()"
   *
   * @return Arrayy
   */
  public function getRandomValue()
  {
    return $this->randomValue();
  }

  /**
   * Pick a random value from the values of this array.
   *
   * @return Arrayy
   */
  public function randomValue()
  {
    return $this->random(1);
  }

  /**
   * alias: for "Arrayy->randomValues()"
   *
   * @param int $number
   *
   * @return Arrayy
   */
  public function getRandomValues($number)
  {
    return $this->randomValues($number);
  }

  /**
   * Pick a given number of random values out of this array.
   *
   * @param int $number
   *
   * @return Arrayy
   */
  public function randomValues($number)
  {
    $number = (int)$number;

    return $this->random($number);
  }

  /**
   * alias: for "Arrayy->randomKey()"
   *
   * @return Arrayy
   */
  public function getRandomKey()
  {
    return $this->randomKey();
  }

  /**
   * Pick a random key/index from the keys of this array.
   *
   * @return Arrayy
   *
   * @throws \RangeException If array is empty
   */
  public function randomKey()
  {
    return $this->randomKeys(1);
  }

  /**
   * Pick a given number of random keys/indexes out of this array.
   *
   * @param int $number The number of keys/indexes (should be <= $this->count())
   *
   * @return Arrayy
   *
   * @throws \RangeException If array is empty
   */
  public function randomKeys($number)
  {
    $number = (int)$number;
    $count = $this->count();

    if ($number === 0 || $number > $count) {
      throw new \RangeException(
          sprintf(
              'Number of requested keys (%s) must be equal or lower than number of elements in this array (%s)',
              $number,
              $count
          )
      );
    }

    $result = (array)array_rand($this->array, $number);

    return static::create($result);
  }

  /**
   * alias: for "Arrayy->randomKeys()"
   *
   * @param int $number
   *
   * @return Arrayy
   */
  public function getRandomKeys($number)
  {
    return $this->randomKeys($number);
  }

  /**
   * find by ...
   *
   * @param        $property
   * @param        $value
   * @param string $comparisonOp
   *
   * @return Arrayy
   */
  public function findBy($property, $value, $comparisonOp = 'eq')
  {
    $array = $this->filterBy($property, $value, $comparisonOp);

    return static::create($array);
  }

  /**
   * Filters an array of objects (or a numeric array of associative arrays) based on the value of a particular property
   * within that.
   *
   * @param        $property
   * @param        $value
   * @param string $comparisonOp
   *
   * @return Arrayy
   */
  public function filterBy($property, $value, $comparisonOp = null)
  {
    if (!$comparisonOp) {
      $comparisonOp = is_array($value) ? 'contains' : 'eq';
    }

    $ops = array(
        'eq'          => function ($item, $prop, $value) {
          return $item[$prop] === $value;
        },
        'gt'          => function ($item, $prop, $value) {
          return $item[$prop] > $value;
        },
        'gte'         => function ($item, $prop, $value) {
          return $item[$prop] >= $value;
        },
        'lt'          => function ($item, $prop, $value) {
          return $item[$prop] < $value;
        },
        'lte'         => function ($item, $prop, $value) {
          return $item[$prop] <= $value;
        },
        'ne'          => function ($item, $prop, $value) {
          return $item[$prop] !== $value;
        },
        'contains'    => function ($item, $prop, $value) {
          return in_array($item[$prop], (array)$value, true);
        },
        'notContains' => function ($item, $prop, $value) {
          return !in_array($item[$prop], (array)$value, true);
        },
        'newer'       => function ($item, $prop, $value) {
          return strtotime($item[$prop]) > strtotime($value);
        },
        'older'       => function ($item, $prop, $value) {
          return strtotime($item[$prop]) < strtotime($value);
        },
    );

    $result = array_values(
        array_filter(
            (array)$this->array,
            function ($item) use (
                $property,
                $value,
                $ops,
                $comparisonOp
            ) {
              $item = (array)$item;
              $itemArrayy = new Arrayy($item);
              $item[$property] = $itemArrayy->get($property, array());

              return $ops[$comparisonOp]($item, $property, $value);
            }
        )
    );

    return static::create($result);
  }

  /**
   * Get a value from an array (optional using dot-notation).
   *
   * @param string $key     The key to look for
   * @param mixed  $default Default value to fallback to
   * @param array  $array   The array to get from,
   *                        if it's set to "null" we use the current array from the class
   *
   * @return mixed
   */
  public function get($key, $default = null, $array = null)
  {
    if (is_array($array) === true) {
      $usedArray = $array;
    } else {
      $usedArray = $this->array;
    }

    if (null === $key) {
      return $usedArray;
    }

    if (isset($usedArray[$key])) {
      return $usedArray[$key];
    }

    // Crawl through array, get key according to object or not
    foreach (explode('.', $key) as $segment) {
      if (!isset($usedArray[$segment])) {
        return $default instanceof Closure ? $default() : $default;
      }

      $usedArray = $usedArray[$segment];
    }

    return $usedArray;
  }

  /**
   * WARNING: Creates a Arrayy object by reference.
   *
   * @param array $array
   *
   * @return $this
   */
  public function createByReference(&$array = array())
  {
    $array = $this->fallbackForArray($array);

    $this->array = &$array;

    return $this;
  }

  /**
   * Get all values from a array.
   *
   * @return Arrayy
   */
  public function values()
  {
    $array = array_values((array)$this->array);

    return static::create($array);
  }

  /**
   * Group values from a array according to the results of a closure.
   *
   * @param string $grouper a callable function name
   * @param bool   $saveKeys
   *
   * @return Arrayy
   */
  public function group($grouper, $saveKeys = false)
  {
    $array = (array)$this->array;
    $result = array();

    // Iterate over values, group by property/results from closure
    foreach ($array as $key => $value) {
      $groupKey = is_callable($grouper) ? $grouper($value, $key) : $this->get($grouper, null, $value);
      $newValue = $this->get($groupKey, null, $result);

      // Add to results
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
   * Given a list and an iterate-function that returns
   * a key for each element in the list (or a property name),
   * returns an object with an index of each item.
   *
   * Just like groupBy, but for when you know your keys are unique.
   *
   * @param mixed $key
   *
   * @return Arrayy
   */
  public function indexBy($key)
  {
    $results = array();

    foreach ($this->array as $a) {
      if (isset($a[$key])) {
        $results[$a[$key]] = $a;
      }
    }

    return static::create($results);
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
   * Implodes array to a string with specified separator.
   *
   * @param string $separator The element's separator
   *
   * @return string The string representation of array, separated by ","
   */
  public function toString($separator = ',')
  {
    return $this->implode($separator);
  }

  /**
   * Implodes an array.
   *
   * @param string $with What to implode it with
   *
   * @return string
   */
  public function implode($with = '')
  {
    return implode($with, $this->array);
  }

  /**
   * Push one or more values onto the end of array at once.
   *
   * @return $this An Arrayy object with pushed elements to the end of array
   */
  public function push(/* variadic arguments allowed */)
  {
    if (func_num_args()) {
      $args = array_merge(array(&$this->array), func_get_args());
      call_user_func_array('array_push', $args);
    }

    return $this;
  }

  /**
   * Shifts a specified value off the beginning of array.
   *
   * @return mixed A shifted element from the current array.
   */
  public function shift()
  {
    return array_shift($this->array);
  }

  /**
   * Prepends one or more values to the beginning of array at once.
   *
   * @return Arrayy Array object with prepended elements to the beginning of array
   */
  public function unshift(/* variadic arguments allowed */)
  {
    if (func_num_args()) {
      $args = array_merge(array(&$this->array), func_get_args());
      call_user_func_array('array_unshift', $args);
    }

    return $this;
  }

  /**
   * Get a value by key.
   *
   * @param $key
   *
   * @return mixed
   */
  public function &__get($key)
  {
    return $this->array[$key];
  }

  /**
   * Whether or not an offset exists.
   *
   * @param mixed $offset
   *
   * @return bool
   */
  public function offsetExists($offset)
  {
    return isset($this->array[$offset]);
  }

  /**
   * Assigns a value to the specified element.
   *
   * @param $key
   * @param $value
   */
  public function __set($key, $value)
  {
    $this->array[$key] = $value;
  }

  /**
   * Whether or not an element exists by key.
   *
   * @param $key
   *
   * @return bool
   */
  public function __isset($key)
  {
    return isset($this->array[$key]);
  }

  /**
   * Unset element by key
   *
   * @param mixed $key
   */
  public function __unset($key)
  {
    unset($this->array[$key]);
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
      } else {
        return false;
      }
    }

    return (array)$this->array;
  }

  /**
   * Search for the value of the current array via $index.
   *
   * @param mixed $index
   *
   * @return Arrayy will return a empty Arrayy if the value wasn't found
   */
  public function searchValue($index)
  {
    // init
    $return = array();

    if (null !== $index) {
      $keyExists = isset($this->array[$index]);

      if ($keyExists !== false) {
        $return = array($this->array[$index]);
      }
    }

    return static::create($return);
  }

  /**
   * Check if all items in current array match a truth test.
   *
   * @param \Closure $closure
   *
   * @return bool
   */
  public function matches(\Closure $closure)
  {
    // Reduce the array to only booleans
    $array = $this->each($closure);

    // Check the results
    if (count($array) === 0) {
      return true;
    }

    $array = array_search(false, $array->toArray(), false);

    return is_bool($array);
  }

  /**
   * Unset an offset.
   *
   * @param mixed $offset
   */
  public function offsetUnset($offset)
  {
    if ($this->offsetExists($offset)) {
      unset($this->array[$offset]);
    }
  }

  /**
   * Iterate over the current array and modify the array's value.
   *
   * @param \Closure $closure
   *
   * @return Arrayy
   */
  public function each(\Closure $closure)
  {
    $array = $this->array;

    foreach ($array as $key => &$value) {
      $value = $closure($value, $key);
    }

    return static::create($array);
  }

  /**
   * alias: for "Arrayy->getArray()"
   */
  public function toArray()
  {
    return $this->getArray();
  }

  /**
   * Check if any item in the current array matches a truth test.
   *
   * @param \Closure $closure
   *
   * @return bool
   */
  public function matchesAny(\Closure $closure)
  {
    // Reduce the array to only booleans
    $array = $this->each($closure);

    // Check the results
    if (count($array) === 0) {
      return true;
    }

    $array = array_search(true, $array->toArray(), false);

    return is_int($array);
  }

  /**
   * Check if we have named keys in the current array.
   *
   * @return bool
   */
  public function isAssoc()
  {
    if (count($this->array) === 0) {
      return false;
    }

    return (bool)count(array_filter(array_keys($this->array), 'is_string'));
  }

  /**
   * Check whether array is numeric or not.
   *
   * @return bool Returns true if numeric, false otherwise
   */
  public function isNumeric()
  {
    $isNumeric = true;

    if ($this->isEmpty()) {
      $isNumeric = false;
    } else {
      foreach ($this->getKeys() as $key) {
        if (!is_int($key)) {
          $isNumeric = false;
          break;
        }
      }
    }

    return $isNumeric;
  }

  /**
   * Check whether the array is empty or not.
   *
   * @return bool Returns true if empty, false otherwise
   */
  public function isEmpty()
  {
    return !$this->array;
  }

  /**
   * Returns the value at specified offset.
   *
   * @param mixed $offset
   *
   * @return mixed return null if the offset did not exists
   */
  public function offsetGet($offset)
  {
    return $this->offsetExists($offset) ? $this->array[$offset] : null;
  }

  /**
   * alias: for "Arrayy->keys()"
   *
   * @return Arrayy
   */
  public function getKeys()
  {
    return $this->keys();
  }

  /**
   * Get all keys from the current array.
   *
   * @return Arrayy
   */
  public function keys()
  {
    $array = array_keys((array)$this->array);

    return static::create($array);
  }

  /**
   * Check if the current array is a multi-array.
   *
   * @return bool
   */
  public function isMultiArray()
  {
    return !(count($this->array) === count($this->array, COUNT_RECURSIVE));
  }

  /**
   * Check if an item is in the current array.
   *
   * @param mixed $value
   *
   * @return bool
   */
  public function contains($value)
  {
    return in_array($value, $this->array, true);
  }

  /**
   * Check if the given key/index exists in the array.
   *
   * @param mixed $key Key/index to search for
   *
   * @return bool Returns true if the given key/index exists in the array, false otherwise
   */
  public function containsKey($key)
  {
    return array_key_exists($key, $this->array);
  }

  /**
   * Returns the average value of the current array.
   *
   * @param int $decimals The number of decimals to return
   *
   * @return int|double The average value
   */
  public function average($decimals = null)
  {
    $count = $this->count();

    if (!$count) {
      return 0;
    }

    if (!is_int($decimals)) {
      $decimals = null;
    }

    return round(array_sum($this->array) / $count, $decimals);
  }

  /**
   * Returns a new ArrayIterator, thus implementing the IteratorAggregate interface.
   *
   * @return \ArrayIterator An iterator for the values in the array.
   */
  public function getIterator()
  {
    return new \ArrayIterator($this->array);
  }

  /**
   * Count the values from the current array.
   *
   * INFO: only a alias for "$arrayy->size()"
   *
   * @return int
   */
  public function length()
  {
    return $this->size();
  }

  /**
   * Get the max value from an array.
   *
   * @return mixed
   */
  public function max()
  {
    if ($this->count() === 0) {
      return false;
    }

    return max($this->array);
  }

  /**
   * Get the min value from an array.
   *
   * @return mixed
   */
  public function min()
  {
    if ($this->count() === 0) {
      return false;
    }

    return min($this->array);
  }

  /**
   * Find the first item in an array that passes the truth test,
   *  otherwise return false
   *
   * @param \Closure $closure
   *
   * @return mixed|false false if we did not find the value
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
   * WARNING!!! -> Clear the current array.
   *
   * @return $this will always return an empty Arrayy object
   */
  public function clear()
  {
    $this->array = array();

    return $this;
  }

  /**
   * Clean all falsy values from an array.
   *
   * @return Arrayy
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
   * Find all items in an array that pass the truth test.
   *
   * @param \Closure|null $closure
   *
   * @return Arrayy
   */
  public function filter($closure = null)
  {
    if (!$closure) {
      return $this->clean();
    }

    $array = array_filter($this->array, $closure);

    return static::create($array);
  }

  /**
   * Get a random value from an array, with the ability to skew the results.
   *
   * Example: randomWeighted(['foo' => 1, 'bar' => 2]) has a 66% chance of returning bar.
   *
   * @param array    $array
   * @param null|int $number how many values you will take?
   *
   * @return Arrayy
   */
  public function randomWeighted(array $array, $number = null)
  {
    $options = array();
    foreach ($array as $option => $weight) {
      if ($this->searchIndex($option)->count() > 0) {
        for ($i = 0; $i < $weight; ++$i) {
          $options[] = $option;
        }
      }
    }

    return $this->mergeAppendKeepIndex($options)->random($number);
  }

  /**
   * Search for the first index of the current array via $value.
   *
   * @param mixed $value
   *
   * @return Arrayy will return a empty Arrayy if the index was not found
   */
  public function searchIndex($value)
  {
    $key = array_search($value, $this->array, true);

    if ($key === false) {
      $return = array();
    } else {
      $return = array($key);
    }

    return static::create($return);
  }

  /**
   * Merge the new $array into the current array.
   *
   * - keep key,value from the current array, also if the index is in the new $array
   *
   * @param array $array
   * @param bool  $recursive
   *
   * @return Arrayy
   */
  public function mergeAppendKeepIndex(array $array = array(), $recursive = false)
  {
    if (true === $recursive) {
      $result = array_replace_recursive($this->array, $array);
    } else {
      $result = array_replace($this->array, $array);
    }

    return static::create($result);
  }

  /**
   * alias: for "Arrayy->searchIndex()"
   *
   * @param mixed $value Value to search for
   *
   * @return Arrayy will return a empty Arrayy if the index was not found
   */
  public function indexOf($value)
  {
    return $this->searchIndex($value);
  }

  /**
   * Return a boolean flag which indicates whether the two input arrays have any common elements.
   *
   * @param array $search
   *
   * @return bool
   */
  public function intersects(array $search)
  {
    return count($this->intersection($search)->array) > 0;
  }

  /**
   * Return an array with all elements found in input array.
   *
   * @param array $search
   *
   * @return Arrayy
   */
  public function intersection(array $search)
  {
    $result = array_values(array_intersect($this->array, $search));

    return static::create($result);
  }

  /**
   * Get the last value(s) from the current array.
   *
   * @param int|null $number
   *
   * @return Arrayy
   */
  public function last($number = null)
  {
    if ($number === null) {
      $poppedValue = (array)$this->pop();
      $arrayy = static::create($poppedValue);
    } else {
      $number = (int)$number;
      $arrayy = $this->rest(-$number);
    }

    return $arrayy;
  }

  /**
   * Pop a specified value off the end of the current array.
   *
   * @return mixed The popped element from the current array.
   */
  public function pop()
  {
    return array_pop($this->array);
  }

  /**
   * Get the last elements from index $from until the end of this array.
   *
   * @param int $from
   *
   * @return Arrayy
   */
  public function rest($from = 1)
  {
    $result = array_splice($this->array, $from);

    return static::create($result);
  }

  /**
   * Get everything but the last..$to items.
   *
   * @param int $to
   *
   * @return Arrayy
   */
  public function initial($to = 1)
  {
    $slice = count($this->array) - $to;

    return $this->first($slice);
  }

  /**
   * Extract a slice of the array.
   *
   * @param int      $offset       Slice begin index
   * @param int|null $length       Length of the slice
   * @param bool     $preserveKeys Whether array keys are preserved or no
   *
   * @return static A slice of the original array with length $length
   */
  public function slice($offset, $length = null, $preserveKeys = false)
  {
    $result = array_slice($this->array, $offset, $length, $preserveKeys);

    return static::create($result);
  }

  /**
   * Iterate over an array and execute a callback for each loop.
   *
   * @param \Closure $closure
   *
   * @return Arrayy
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
   * Merge the new $array into the current array.
   *
   * - replace duplicate assoc-keys from the current array with the key,values from the new $array
   * - create new indexes
   *
   * @param array $array
   * @param bool  $recursive
   *
   * @return Arrayy
   */
  public function mergeAppendNewIndex(array $array = array(), $recursive = false)
  {
    if (true === $recursive) {
      $result = array_merge_recursive($this->array, $array);
    } else {
      $result = array_merge($this->array, $array);
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
   * @return Arrayy
   */
  public function mergePrependNewIndex(array $array = array(), $recursive = false)
  {
    if (true === $recursive) {
      $result = array_merge_recursive($array, $this->array);
    } else {
      $result = array_merge($array, $this->array);
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
   * @return Arrayy
   */
  public function mergePrependKeepIndex(array $array = array(), $recursive = false)
  {
    if (true === $recursive) {
      $result = array_replace_recursive($array, $this->array);
    } else {
      $result = array_replace($array, $this->array);
    }

    return static::create($result);
  }

  /**
   * Return values that are only in the current array.
   *
   * @param array $array
   *
   * @return Arrayy
   */
  public function diff(array $array = array())
  {
    $result = array_diff($this->array, $array);

    return static::create($result);
  }

  /**
   * Return values that are only in the new $array.
   *
   * @param array $array
   *
   * @return Arrayy
   */
  public function diffReverse(array $array = array())
  {
    $result = array_diff($array, $this->array);

    return static::create($result);
  }

  /**
   * Replace the first matched value in an array.
   *
   * @param mixed $search
   * @param mixed $replacement
   *
   * @return Arrayy
   */
  public function replaceOneValue($search, $replacement = '')
  {
    $array = $this->array;
    $key = array_search($search, $array, true);

    if ($key !== false) {
      $array[$key] = $replacement;
    }

    return static::create($array);
  }

  /**
   * Replace values in the current array.
   *
   * @param string $search      The string to replace.
   * @param string $replacement What to replace it with.
   *
   * @return Arrayy
   */
  public function replaceValues($search, $replacement = '')
  {
    $array = $this->each(
        function ($value) use ($search, $replacement) {
          return UTF8::str_replace($search, $replacement, $value);
        }
    );

    return static::create($array);
  }

  /**
   * Replace the keys in an array with another set.
   *
   * @param array $keys An array of keys matching the array's size
   *
   * @return Arrayy
   */
  public function replaceKeys(array $keys)
  {
    $values = array_values($this->array);
    $result = array_combine($keys, $values);

    return static::create($result);
  }

  /**
   * Create an array using the current array as keys and the other array as values.
   *
   * @param array $array Values array
   *
   * @return Arrayy Arrayy object with values from the other array.
   */
  public function replaceAllValues(array $array)
  {
    $result = array_combine($this->array, $array);

    return static::create($result);
  }

  /**
   * Create an array using the current array as values and the other array as keys.
   *
   * @param array $keys Keys array
   *
   * @return Arrayy Arrayy object with keys from the other array.
   */
  public function replaceAllKeys(array $keys)
  {
    $result = array_combine($keys, $this->array);

    return static::create($result);
  }

  /**
   * Shuffle the current array.
   *
   * @return Arrayy
   */
  public function shuffle()
  {
    $array = $this->array;

    shuffle($array);

    return static::create($array);
  }

  /**
   * Split an array in the given amount of pieces.
   *
   * @param int  $numberOfPieces
   * @param bool $keepKeys
   *
   * @return array
   */
  public function split($numberOfPieces = 2, $keepKeys = false)
  {
    if (count($this->array) === 0) {
      $result = array();
    } else {
      $numberOfPieces = (int)$numberOfPieces;
      $splitSize = ceil(count($this->array) / $numberOfPieces);
      $result = array_chunk($this->array, $splitSize, $keepKeys);
    }

    return static::create($result);
  }

  /**
   * Create a chunked version of this array.
   *
   * @param int  $size         Size of each chunk
   * @param bool $preserveKeys Whether array keys are preserved or no
   *
   * @return static A new array of chunks from the original array
   */
  public function chunk($size, $preserveKeys = false)
  {
    $result = array_chunk($this->array, $size, $preserveKeys);

    return static::create($result);
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
   * @return Arrayy
   */
  public function getColumn($columnKey = null, $indexKey = null)
  {
    $result = array_column($this->array, $columnKey, $indexKey);

    return static::create($result);
  }

  /**
   * Invoke a function on all of an array's values.
   *
   * @param mixed $callable
   * @param array $arguments
   *
   * @return Arrayy
   */
  public function invoke($callable, $arguments = array())
  {
    // If one argument given for each iteration, create an array for it.
    if (!is_array($arguments)) {
      $arguments = StaticArrayy::repeat($arguments, count($this->array))->getArray();
    }

    // If the callable has arguments, pass them.
    if ($arguments) {
      $array = array_map($callable, $this->array, $arguments);
    } else {
      $array = array_map($callable, $this->array);
    }

    return static::create($array);
  }

  /**
   * Apply the given function to the every element of the array,
   * collecting the results.
   *
   * @param callable $callable
   *
   * @return Arrayy Arrayy object with modified elements
   */
  public function map($callable)
  {
    $result = array_map($callable, $this->array);

    return static::create($result);
  }

  /**
   * Check if a value is in the current array using a closure.
   *
   * @param \Closure $closure
   *
   * @return bool Returns true if the given value is found, false otherwise
   */
  public function exists(\Closure $closure)
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
   * Return all items that fail the truth test.
   *
   * @param \Closure $closure
   *
   * @return Arrayy
   */
  public function reject(\Closure $closure)
  {
    $filtered = array();

    foreach ($this->array as $key => $value) {
      if (!$closure($value, $key)) {
        $filtered[$key] = $value;
      }
    }

    return static::create($filtered);
  }

  /**
   * Replace a key with a new key/value pair.
   *
   * @param $replace
   * @param $key
   * @param $value
   *
   * @return Arrayy
   */
  public function replace($replace, $key, $value)
  {
    $this->remove($replace);

    return $this->set($key, $value);
  }

  /**
   * Remove a value from the current array (optional using dot-notation).
   *
   * @param mixed $key
   *
   * @return Arrayy
   */
  public function remove($key)
  {
    // Recursive call
    if (is_array($key)) {
      foreach ($key as $k) {
        $this->internalRemove($k);
      }

      return static::create($this->array);
    }

    $this->internalRemove($key);

    return static::create($this->array);
  }

  /**
   * Internal mechanics of remove method.
   *
   * @param $key
   *
   * @return boolean
   */
  protected function internalRemove($key)
  {
    // Explode keys
    $keys = explode('.', $key);

    // Crawl though the keys
    while (count($keys) > 1) {
      $key = array_shift($keys);

      if (!$this->has($key)) {
        return false;
      }

      $this->array = &$this->array[$key];
    }

    $key = array_shift($keys);

    unset($this->array[$key]);

    return true;
  }

  /**
   * Check if an array has a given key.
   *
   * @param mixed $key
   *
   * @return bool
   */
  public function has($key)
  {
    // Generate unique string to use as marker.
    $unFound = (string)uniqid('arrayy', true);

    return $this->get($key, $unFound) !== $unFound;
  }

  /**
   * Set a value for the current array (optional using dot-notation).
   *
   * @param string $key   The key to set
   * @param mixed  $value Its value
   *
   * @return Arrayy
   */
  public function set($key, $value)
  {
    $this->internalSet($key, $value);

    return static::create($this->array);
  }

  /**
   * Internal mechanic of set method.
   *
   * @param mixed $key
   * @param mixed $value
   *
   * @return bool
   */
  protected function internalSet($key, $value)
  {
    if (null === $key) {
      return false;
    }

    // Explode the keys
    $keys = explode('.', $key);

    // Crawl through the keys
    while (count($keys) > 1) {
      $key = array_shift($keys);

      $this->array[$key] = $this->get(array(), null, $key);
      $this->array = &$this->array[$key];
    }

    // Bind final tree on the array
    $key = array_shift($keys);

    $this->array[$key] = $value;

    return true;
  }

  /**
   * Get a value from a array and set it if it was not.
   *
   * WARNING: this method only set the value, if the $key is not already set
   *
   * @param string $key     The key
   * @param mixed  $default The default value to set if it isn't
   *
   * @return mixed
   */
  public function setAndGet($key, $default = null)
  {
    // If the key doesn't exist, set it
    if (!$this->has($key)) {
      $this->array = $this->set($key, $default)->getArray();
    }

    return $this->get($key);
  }

  /**
   * Remove the first value from the current array.
   *
   * @return Arrayy
   */
  public function removeFirst()
  {
    array_shift($this->array);

    return static::create($this->array);
  }

  /**
   * Remove the last value from the current array.
   *
   * @return Arrayy
   */
  public function removeLast()
  {
    array_pop($this->array);

    return static::create($this->array);
  }

  /**
   * Removes a particular value from an array (numeric or associative).
   *
   * @param mixed $value
   *
   * @return Arrayy
   */
  public function removeValue($value)
  {
    $isNumericArray = true;
    foreach ($this->array as $key => $item) {
      if ($item === $value) {
        if (!is_int($key)) {
          $isNumericArray = false;
        }
        unset($this->array[$key]);
      }
    }

    if ($isNumericArray) {
      $this->array = array_values($this->array);
    }

    return static::create($this->array);
  }

  /**
   * Pad array to the specified size with a given value.
   *
   * @param int   $size  Size of the result array
   * @param mixed $value Empty value by default
   *
   * @return Arrayy Arrayy object padded to $size with $value
   */
  public function pad($size, $value)
  {
    $result = array_pad($this->array, $size, $value);

    return static::create($result);
  }

  /**
   * Prepend a value to an array.
   *
   * @param mixed $value
   *
   * @return Arrayy
   */
  public function prepend($value)
  {
    array_unshift($this->array, $value);

    return static::create($this->array);
  }

  /**
   * alias: for "Arrayy->append()"
   *
   * @param $value
   *
   * @return $this
   */
  public function add($value)
  {
    $this->array[] = $value;

    return $this;
  }

  /**
   * Create a numerically re-indexed Arrayy object.
   *
   * @return Arrayy The new instance with re-indexed array-elements
   */
  public function reindex()
  {
    $this->array = array_values($this->array);

    return static::create($this->array);
  }

  /**
   * Return the array in the reverse order.
   *
   * @return Arrayy
   */
  public function reverse()
  {
    $this->array = array_reverse($this->array);

    return static::create($this->array);
  }

  /**
   * Custom sort by value via "usort"
   *
   * @link http://php.net/manual/en/function.usort.php
   *
   * @param callable $func
   *
   * @return $this
   */
  public function customSortValues(callable $func)
  {
    usort($this->array, $func);

    return $this;
  }

  /**
   * Custom sort by index via "uksort"
   *
   * @link http://php.net/manual/en/function.uksort.php
   *
   * @param callable $func
   *
   * @return $this
   */
  public function customSortKeys(callable $func)
  {
    uksort($this->array, $func);

    return $this;
  }

  /**
   * Sort the current array by key.
   *
   * @link http://php.net/manual/en/function.ksort.php
   * @link http://php.net/manual/en/function.krsort.php
   *
   * @param int|string $direction use SORT_ASC or SORT_DESC
   * @param int        $strategy  use e.g.: SORT_REGULAR or SORT_NATURAL
   *
   * @return $this
   */
  public function sortKeys($direction = SORT_ASC, $strategy = SORT_REGULAR)
  {
    $this->sorterKeys($this->array, $direction, $strategy);

    return $this;
  }

  /**
   * sorting keys
   *
   * @param array $elements
   * @param int   $direction
   * @param int   $strategy
   */
  protected function sorterKeys(array &$elements, $direction = SORT_ASC, $strategy = SORT_REGULAR)
  {
    $direction = $this->getDirection($direction);

    switch ($direction) {
      case 'desc':
      case SORT_DESC:
        krsort($elements, $strategy);
        break;
      case 'asc':
      case SORT_ASC:
      default:
        ksort($elements, $strategy);
    }
  }

  /**
   * Get correct PHP constant for direction.
   *
   * @param int|string $direction
   *
   * @return int
   */
  protected function getDirection($direction)
  {
    if (is_string($direction)) {
      $direction = strtolower($direction);

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
   * Sort the current array by value.
   *
   * @param int $direction use SORT_ASC or SORT_DESC
   * @param int $strategy  use e.g.: SORT_REGULAR or SORT_NATURAL
   *
   * @return Arrayy
   */
  public function sortValueKeepIndex($direction = SORT_ASC, $strategy = SORT_REGULAR)
  {
    return $this->sort($direction, $strategy, true);
  }

  /**
   * Sort the current array and optional you can keep the keys.
   *
   * @param string|int $direction use SORT_ASC or SORT_DESC
   * @param int|string $strategy
   * @param bool       $keepKeys
   *
   * @return Arrayy
   */
  public function sort($direction = SORT_ASC, $strategy = SORT_REGULAR, $keepKeys = false)
  {
    $this->sorting($this->array, $direction, $strategy, $keepKeys);

    return $this;
  }

  /**
   * @param array      &$elements
   * @param int|string $direction
   * @param int        $strategy
   * @param bool       $keepKeys
   */
  protected function sorting(array &$elements, $direction = SORT_ASC, $strategy = SORT_REGULAR, $keepKeys = false)
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
          rsort($elements, $strategy);
        }
        break;
      case 'asc':
      case SORT_ASC:
      default:
        if ($keepKeys) {
          asort($elements, $strategy);
        } else {
          sort($elements, $strategy);
        }
    }
  }

  /**
   * Sort the current array by value.
   *
   * @param int $direction use SORT_ASC or SORT_DESC
   * @param int $strategy  use e.g.: SORT_REGULAR or SORT_NATURAL
   *
   * @return Arrayy
   */
  public function sortValueNewIndex($direction = SORT_ASC, $strategy = SORT_REGULAR)
  {
    return $this->sort($direction, $strategy, false);
  }

  /**
   * Sort a array by value, by a closure or by a property.
   *
   * - If the sorter is null, the array is sorted naturally.
   * - Associative (string) keys will be maintained, but numeric keys will be re-indexed.
   *
   * @param null       $sorter
   * @param string|int $direction
   * @param int        $strategy
   *
   * @return Arrayy
   */
  public function sorter($sorter = null, $direction = SORT_ASC, $strategy = SORT_REGULAR)
  {
    $array = (array)$this->array;
    $direction = $this->getDirection($direction);

    // Transform all values into their results.
    if ($sorter) {
      $arrayy = new self($array);

      $that = $this;
      $results = $arrayy->each(
          function ($value) use ($sorter, $that) {
            return is_callable($sorter) ? $sorter($value) : $that->get($sorter, null, $value);
          }
      );

      $results = $results->getArray();
    } else {
      $results = $array;
    }

    // Sort by the results and replace by original values
    array_multisort($results, $direction, $strategy, $array);

    return static::create($array);
  }

  /**
   * Exchanges all keys with their associated values in an array.
   *
   * @return Arrayy
   */
  public function flip()
  {
    $this->array = array_flip($this->array);

    return static::create($this->array);
  }

  /**
   * Apply the given function to every element in the array,
   * discarding the results.
   *
   * @param callable $callable
   * @param bool     $recursive Whether array will be walked recursively or no
   *
   * @return Arrayy An Arrayy object with modified elements
   */
  public function walk($callable, $recursive = false)
  {
    if (true === $recursive) {
      array_walk_recursive($this->array, $callable);
    } else {
      array_walk($this->array, $callable);
    }

    return $this;
  }

  /**
   * Reduce the current array via callable e.g. anonymous-function.
   *
   * @param mixed $predicate
   * @param array $init
   *
   * @return Arrayy
   */
  public function reduce($predicate, array $init = array())
  {
    $this->array = array_reduce($this->array, $predicate, $init);

    return static::create($this->array);
  }

  /**
   * Return a duplicate free copy of the current array.
   *
   * @return Arrayy
   */
  public function unique()
  {
    $this->array = array_reduce(
        $this->array,
        function ($resultArray, $value) {
          if (in_array($value, $resultArray, true) === false) {
            $resultArray[] = $value;
          }

          return $resultArray;
        },
        array()
    );

    return static::create($this->array);
  }

  /**
   * Convert the current array to JSON.
   *
   * @param null $options e.g. JSON_PRETTY_PRINT
   *
   * @return string
   */
  public function toJson($options = null)
  {
    return UTF8::json_encode($this->array, $options);
  }
}
