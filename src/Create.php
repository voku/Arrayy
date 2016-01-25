<?php

namespace Arrayy;

if (!function_exists('Arrayy\create')) {
  /**
   * Creates a Arrayy object
   *
   * @param $array
   *
   * @return Arrayy
   */
  function create(array $array)
  {
    return new Arrayy($array);
  }
}
