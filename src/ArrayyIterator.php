<?php

namespace Arrayy;

/**
 * Class ArrayyIterator
 *
 * @package Arrayy
 */
class ArrayyIterator extends \ArrayIterator
{
  /**
   * ArrayyIterator constructor.
   *
   * @param array $array
   * @param int   $flags
   */
  public function __construct(array $array = array(), $flags = 0)
  {
    parent::__construct($array, $flags);
  }

  /**
   * @return Arrayy|mixed will return a "Arrayy"-object instead of an array
   */
  public function current()
  {
    $value = parent::current();

    if (is_array($value)) {
      $value = Arrayy::create($value);
    }

    return $value;
  }

  /**
   * @param string $offset
   *
   * @return Arrayy|mixed will return a "Arrayy"-object instead of an array
   */
  public function offsetGet($offset)
  {
    $value = parent::offsetGet($offset);

    if (is_array($value)) {
      $value = Arrayy::create($value);
    }

    return $value;
  }
};
