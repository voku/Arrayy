<?php

use Arrayy\Arrayy as A;

/**
 * Class ArrayyTestCase
 */
class ArrayyAbstractTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider hasProvider()
   *
   * @param mixed $expected
   * @param array $array
   * @param mixed $key
   */
  public function testHas($expected, $array, $key)
  {
    $arrayy = new A($array);
    self::assertEquals($expected, $arrayy->has($key));
  }

  /**
   * @return array
   */
  public function hasProvider()
  {
    return array(
        array(false, array(null), 0),
        array(true, array(false), 0),
        array(false, array(true), 1),
        array(false, array(false), 1),
        array(true, array(true), 0),
        array(true, array(-9, 1, 0, false), 1),
        array(true, array(1.18), 0),
        array(false, array(' string  ', 'foo'), 'foo'),
        array(true, array(' string  ', 'foo' => 'foo'), 'foo'),
    );
  }

  /**
   * @dataProvider getProvider()
   *
   * @param mixed $expected
   * @param array $array
   * @param mixed $key
   */
  public function testGet($expected, $array, $key)
  {
    $arrayy = new A($array);
    self::assertEquals($expected, $arrayy->get($key));
  }

  /**
   * @return array
   */
  public function getProvider()
  {
    return array(
        array(null, array(null), 0),
        array(false, array(false), 0),
        array(null, array(true), 1),
        array(null, array(false), 1),
        array(true, array(true), 0),
        array(1, array(-9, 1, 0, false), 1),
        array(1.18, array(1.18), 0),
        array(false, array(' string  ', 'foo'), 'foo'),
        array('foo', array(' string  ', 'foo' => 'foo'), 'foo'),
    );
  }

  /**
   * @dataProvider setProvider()
   *
   * @param array $array
   * @param mixed $key
   * @param mixed $value
   */
  public function testSet($array, $key, $value)
  {
    $arrayy = new A($array);
    $arrayy = $arrayy->set($key, $value)->getArray();
    self::assertEquals($value, $arrayy[$key]);
  }

  /**
   * @return array
   */
  public function setProvider()
  {
    return array(
        array(array(null), 0, 'foo'),
        array(array(false), 0, true),
        array(array(true), 1, 'foo'),
        array(array(false), 1, 'foo'),
        array(array(true), 0, 'foo'),
        array(array(-9, 1, 0, false), 1, 'foo'),
        array(array(1.18), 0, 1),
        array(array(' string  ', 'foo'), 'foo', 'lall'),
        array(array(' string  ', 'foo' => 'foo'), 'foo', 'lall'),
    );
  }
}
