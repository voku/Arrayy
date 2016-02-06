<?php

namespace Arrayy;

use Closure;

/**
 * Abstract Arrayy
 * Methods that apply to both objects and arrays.
 */
abstract class ArrayyAbstract
{
  /**
   * @var array
   */
  protected $array = array();

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// ANALYZE //////////////////////////////
  ////////////////////////////////////////////////////////////////////

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

  ////////////////////////////////////////////////////////////////////
  //////////////////////////// FETCH FROM ////////////////////////////
  ////////////////////////////////////////////////////////////////////

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
   * Set a value in a array using dot notation.
   *
   * @param string $key   The key to set
   * @param mixed  $value Its value
   *
   * @return Arrayy
   */
  public function set($key, $value)
  {
    $this->internalSet($key, $value);

    return Arrayy::create($this->array);
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
   * Remove a value from an array using dot notation.
   *
   * @param mixed $key
   *
   * @return mixed
   */
  public function remove($key)
  {
    // Recursive call
    if (is_array($key)) {
      foreach ($key as $k) {
        $this->internalRemove($k);
      }

      return $this->array;
    }

    $this->internalRemove($key);

    return $this->array;
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

    return Arrayy::create($result);
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
    $return = $this->filterBy($property, $value, $comparisonOp);

    return Arrayy::create($return);
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// ANALYZE //////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get all keys from the current array.
   *
   * @return Arrayy
   */
  public function keys()
  {
    $return = array_keys((array)$this->array);

    return Arrayy::create($return);
  }

  /**
   * Get all values from a array.
   *
   * @return Arrayy
   */
  public function values()
  {
    $return = array_values((array)$this->array);

    return Arrayy::create($return);
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// ALTER ///////////////////////////////
  ////////////////////////////////////////////////////////////////////

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

    return Arrayy::create($result);
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Internal mechanic of set method.
   *
   * @param string $key
   * @param mixed  $value
   *
   * @return mixed
   */
  protected function internalSet($key, $value)
  {
    if (null === $key) {
      /** @noinspection OneTimeUseVariablesInspection */
      $array = $value;

      return $array;
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
   * Given a list, and an iteratee function that returns
   * a key for each element in the list (or a property name),
   * returns an object with an index of each item.
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

    return Arrayy::create($results);
  }
}
