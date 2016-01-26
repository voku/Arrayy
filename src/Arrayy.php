<?php

namespace Arrayy;

use ArrayAccess;
use voku\helper\UTF8;

/**
 * Methods to manage arrays.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Arrayy extends ArrayyAbstract implements \Countable, \IteratorAggregate, \ArrayAccess
{
  /**
   * Initializes
   *
   * @param array $array
   */
  public function __construct($array = array())
  {
    if (!$array) {
      $array = array();
    }

    if (
        is_string($array)
        ||
        (is_object($array) && method_exists($array, '__toString'))
    ) {
      $array = (array)$array;
    }

    if (!is_array($array)) {
      throw new \InvalidArgumentException(
          'Passed value must be a array'
      );
    }

    $this->array = $array;
  }

  /**
   * magic to string
   *
   * @return string
   */
  public function __toString()
  {
    return $this->implode(',');
  }

  /**
   * Get a data by key
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
   * Assigns a value to the specified data
   *
   * @param $key
   * @param $value
   */
  public function __set($key, $value)
  {
    $this->array[$key] = $value;
  }

  /**
   * Whether or not an data exists by key
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
   * Unsets an data by key
   *
   * @param mixed $key
   */
  public function __unset($key)
  {
    unset($this->array[$key]);
  }

  /**
   * Assigns a value to the specified offset
   *
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
   * Whether or not an offset exists
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
   * Unsets an offset
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
   * Returns the value at specified offset
   *
   * @param mixed $offset
   *
   * @return null
   */
  public function offsetGet($offset)
  {
    return $this->offsetExists($offset) ? $this->array[$offset] : null;
  }

  /**
   * Returns a new ArrayIterator, thus implementing the IteratorAggregate
   * interface.
   *
   * @return \ArrayIterator An iterator for the values in the array
   */
  public function getIterator()
  {
    return new \ArrayIterator($this->array);
  }

  /**
   * call object as function
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
   * get the current array from the "Arrayy"-object
   *
   * @return array
   */
  public function getArray()
  {
    return $this->array;
  }

  /**
   * Creates a Arrayy object
   *
   * @param array $array
   *
   * @return self
   */
  public static function create($array = array())
  {
    return new static($array);
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// ANALYZE //////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Search for the value of the current array via $index.
   *
   * @param mixed $index
   *
   * @return self
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

    return self::create((array)$return);
  }

  /**
   * Search for the index of the current array via $value.
   *
   * @param mixed $value
   *
   * @return self
   */
  public function searchIndex($value)
  {
    $key = array_search($value, $this->array, true);

    if ($key === false) {
      $return = array();
    } else {
      $return = array($key);
    }

    return self::create((array)$return);
  }

  /**
   * Check if all items in an array match a truth test.
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
    $array = array_search(false, $array, false);

    return is_bool($array);
  }

  /**
   * Check if any item in an array matches a truth test.
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
    $array = array_search(true, $array, false);

    return is_int($array);
  }

  /**
   * Check if an item is in an array.
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
   * Returns the average value of an array.
   *
   * @param int $decimals The number of decimals to return
   *
   * @return int The average value
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

  ////////////////////////////////////////////////////////////////////
  //////////////////////////// FETCH FROM ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Find the first item in an array that passes the truth test,
   *  otherwise return false
   *
   * @param \Closure $closure
   *
   * @return mixed|false false if we couldn't find the value
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
   * Clean all falsy values from an array.
   *
   * @return self
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
   * Get a random string from an array.
   *
   * @param null $take
   *
   * @return self
   */
  public function random($take = null)
  {
    if ($take !== null) {
      return $this->array[array_rand($this->array)];
    }

    shuffle($this->array);

    return $this->first($take);
  }

  /**
   * Return an array with all elements found in input array.
   *
   * @param array $search
   *
   * @return self
   */
  public function intersection(array $search)
  {
    return self::create(array_values(array_intersect($this->array, $search)));
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

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// SLICERS //////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get the first value from an array.
   *
   * @param int|null $take
   *
   * @return self
   */
  public function first($take = null)
  {
    if ($take === null) {
      $array = array_shift($this->array);
    } else {
      $array = array_splice($this->array, 0, $take, true);
    }

    return self::create((array)$array);
  }

  /**
   * Get the last value from an array.
   *
   * @param int|null $take
   *
   * @return self
   */
  public function last($take = null)
  {
    if ($take === null) {
      $array = self::create((array)array_pop($this->array));
    } else {
      $array = $this->rest(-$take);
    }

    return $array;
  }

  /**
   * Get everything but the last..$to items.
   *
   * @param int $to
   *
   * @return self
   */
  public function initial($to = 1)
  {
    $slice = count($this->array) - $to;

    return $this->first($slice);
  }

  /**
   * Get the last elements from index $from until the end of this array.
   *
   * @param int $from
   *
   * @return self
   */
  public function rest($from = 1)
  {
    return self::create(array_splice($this->array, $from));
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// ACT UPON /////////////////////////////
  ////////////////////////////////////////////////////////////////////

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

    return self::create($array);
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// ALTER ///////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Merge the new $array into the current array.
   *
   * - replace duplicate keys from the current array with the key,values from the new $array
   * - create new indexes
   *
   * @param array $array
   *
   * @return self
   */
  public function mergeAppendNewIndex(array $array = array())
  {
    return self::create(array_merge($this->array, $array));
  }

  /**
   * Merge the current array into the new $array.
   *
   * - replace duplicate keys from new $array with the key,values from the current array
   * - create new indexes
   *
   * @param array $array
   *
   * @return self
   */
  public function mergePrependNewIndex(array $array = array())
  {
    return self::create(array_merge($array, $this->array));
  }

  /**
   * Merge the new $array into the current array.
   *
   * - keep key,value from the current array, also if the index is in the new $array
   *
   * @param array $array
   *
   * @return self
   */
  public function mergeAppendKeepIndex(array $array = array())
  {
    return self::create(array_replace($array, $this->array));
  }

  /**
   * Merge the the current array into the $array.
   *
   * - use key,value from the new $array, also if the index is in the current array
   *
   * @param array $array
   *
   * @return self
   */
  public function mergePrependKeepIndex(array $array = array())
  {
    return self::create(array_replace($this->array, $array));
  }

  /**
   * Return values that are only in the current array.
   *
   * @param array $array
   *
   * @return self
   */
  public function diff(array $array = array())
  {
    return self::create(array_diff($this->array, $array));
  }

  /**
   * Return values that are only in the new $array.
   *
   * @param array $array
   *
   * @return self
   */
  public function diffReverse(array $array = array())
  {
    return self::create(array_diff($array, $this->array));
  }

  /**
   * Replace the first matched value in an array.
   *
   * @param string $search The string to replace
   * @param string $replacement    What to replace it with
   *
   * @return self
   */
  public function replaceOneValue($search, $replacement = '')
  {
    $array = $this->array;
    $key = array_search($search, $array, true);

    if ($key !== false) {
      $array[$key] = $replacement;
    }

    return self::create((array)$array);
  }

  /**
   * Replace values in an array.
   *
   * @param string $search The string to replace
   * @param string $replacement    What to replace it with
   *
   * @return self
   */
  public function replaceValues($search, $replacement = '')
  {
    $array = $this->each(
        function ($value) use ($search, $replacement) {
          return UTF8::str_replace($search, $replacement, $value);
        }
    );

    return self::create((array)$array);
  }

  /**
   * Replace the keys in an array with another set.
   *
   * @param array $keys An array of keys matching the array's size
   *
   * @return self
   */
  public function replaceKeys(array $keys)
  {
    $values = array_values($this->array);

    return self::create(array_combine($keys, $values));
  }

  /**
   * Iterate over an array and modify the array's value.
   *
   * @param \Closure $closure
   *
   * @return array
   */
  public function each(\Closure $closure)
  {
    $array = $this->array;

    foreach ($array as $key => &$value) {
      $value = $closure($value, $key);
    }

    return $array;
  }

  /**
   * Shuffle an array.
   *
   * @return self
   */
  public function shuffle()
  {
    $array = $this->array;

    shuffle($array);

    return self::create($array);
  }

  /**
   * Sort an array by key.
   *
   * @param string $direction
   *
   * @return self
   */
  public function sortKeys($direction = 'ASC')
  {
    $array = $this->array;
    $direction = strtolower($direction);

    if ($direction === 'desc') {
      $directionType = SORT_DESC;
    } else {
      $directionType = SORT_ASC;
    }

    if ($directionType === SORT_ASC) {
      ksort($array);
    } else {
      krsort($array);
    }

    return self::create($array);
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
   * Find all items in an array that pass the truth test.
   *
   * @param \Closure $closure
   *
   * @return self
   */
  public function filter($closure = null)
  {
    if (!$closure) {
      return $this->clean();
    }

    $array = array_filter($this->array, $closure);

    return self::create($array);
  }

  /**
   * Invoke a function on all of an array's values.
   *
   * @param mixed $callable
   * @param array $arguments
   *
   * @return self
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

    return self::create($array);
  }

  /**
   * Return all items that fail the truth test.
   *
   * @param \Closure $closure
   *
   * @return self
   */
  public function reject(\Closure $closure)
  {
    $filtered = array();

    foreach ($this->array as $key => $value) {
      if (!$closure($value, $key)) {
        $filtered[$key] = $value;
      }
    }

    return self::create($filtered);
  }

  /**
   * Remove the first value from an array.
   *
   * @return self
   */
  public function removeFirst()
  {
    array_shift($this->array);

    return self::create($this->array);
  }

  /**
   * Remove the last value from an array.
   *
   * @return self
   */
  public function removeLast()
  {
    array_pop($this->array);

    return self::create($this->array);
  }

  /**
   * Removes a particular value from an array (numeric or associative).
   *
   * @param mixed $value
   *
   * @return self
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

    return self::create($this->array);
  }

  /**
   * Prepend a value to an array.
   *
   * @param mixed $value
   *
   * @return self
   */
  public function prepend($value)
  {
    array_unshift($this->array, $value);

    return self::create($this->array);
  }

  /**
   * Append a value to an array.
   *
   * @param mixed $value
   *
   * @return self
   */
  public function append($value)
  {
    $this->array[] = $value;

    return self::create($this->array);
  }

  /**
   * Return the array in the reverse order.
   *
   * @return self
   */
  public function reverse()
  {
    $this->array = array_reverse($this->array);

    return self::create($this->array);
  }

  /**
   * duplicate free copy of an array
   *
   * @return self
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

    return self::create($this->array);
  }
}
