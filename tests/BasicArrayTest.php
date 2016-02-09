<?php

use Arrayy\Arrayy as A;

/**
 * Copy of a test class from "https://github.com/bocharsky-bw/Arrayzy/"
 *
 * @author Victor Bocharsky <bocharsky.bw@gmail.com>
 */
class BasicArrayTest extends PHPUnit_Framework_TestCase
{
  const TYPE_ASSOC   = 'assoc';
  const TYPE_EMPTY   = 'empty';
  const TYPE_MIXED   = 'mixed';
  const TYPE_NUMERIC = 'numeric';
  /**
   * @var string
   */
  protected $arrayyClassName = 'Arrayy\Arrayy';

  /**
   * @param A     $arrayy
   * @param A     $resultArrayy
   * @param array $array
   * @param array $resultArray
   */
  protected function assertImmutable(A $arrayy, A $resultArrayy, array $array, array $resultArray)
  {
    self::assertNotSame($arrayy, $resultArrayy);
    self::assertSame($array, $arrayy->toArray());
    self::assertSame($resultArray, $resultArrayy->toArray());
  }

  /**
   * @param A     $arrayy
   * @param A     $resultArrayy
   * @param array $resultArray
   */
  protected function assertMutable(A $arrayy, A $resultArrayy, array $resultArray)
  {
    self::assertSame($arrayy, $resultArrayy);
    self::assertSame($resultArray, $arrayy->toArray());
    self::assertSame($resultArray, $resultArrayy->toArray());
  }

  /**
   * @param array $array
   *
   * @return A
   */
  protected function createArrayy(array $array = array())
  {
    return new $this->arrayyClassName($array);
  }

  /**
   * @return array
   */
  public function simpleArrayProvider()
  {
    return array(
        'empty_array'   => array(
            array(),
            self::TYPE_EMPTY,
        ),
        'indexed_array' => array(
            array(
                1 => 'one',
                2 => 'two',
                3 => 'three',
            ),
            self::TYPE_NUMERIC,
        ),
        'assoc_array'   => array(
            array(
                'one'   => 1,
                'two'   => 2,
                'three' => 3,
            ),
            self::TYPE_ASSOC,
        ),
        'mixed_array'   => array(
            array(
                1     => 'one',
                'two' => 2,
                3     => 'three',
            ),
            self::TYPE_MIXED,
        ),
    );
  }

  /**
   * @return array
   */
  public function stringWithSeparatorProvider()
  {
    return array(
        array(
            's,t,r,i,n,g',
            ',',
        ),
        array(
            'He|ll|o',
            '|',
        ),
        array(
            'Wo;rld',
            ';',
        ),
    );
  }

  // The method list order by ASC

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testContains(array $array)
  {
    $element = 2;

    $arrayy = $this->createArrayy($array);
    $isContains = in_array($element, $array, true);

    self::assertSame($isContains, $arrayy->contains($element));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testContainsKey(array $array)
  {
    $key = 2;

    $arrayy = $this->createArrayy($array);
    $isContainsKey = array_key_exists($key, $array);

    self::assertSame($isContainsKey, $arrayy->containsKey($key));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testCount(array $array)
  {
    $arrayy = $this->createArrayy($array);
    $count = count($array);

    self::assertSame($count, $arrayy->count());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testCurrent(array $array)
  {
    $arrayy = $this->createArrayy($array);
    $current = current($array);

    self::assertSame($current, current($arrayy->getArray()));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testDebugReturn(array $array)
  {
    $arrayy = $this->createArrayy($array);
    $printed = print_r($array, true);

    self::assertSame($printed, print_r($arrayy->toArray(), true));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testExists(array $array)
  {
    $callable = function ($value, $key) {
      return 2 === $key and 'two' === $value;
    };

    $arrayy = $this->createArrayy($array);
    $isExists = isset($array[2]) && 'two' === $array[2];

    self::assertSame($isExists, $arrayy->exists($callable));
  }

  public function testFind()
  {
    $callable = function ($value, $key) {
      return 'a' === $value and 2 < $key;
    };

    $a = $this->createArrayy(array('a', 'b', 'c', 'b', 'a'));
    $found = $a->find($callable);

    self::assertSame('a', $found);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testFirst(array $array)
  {
    $arrayy = $this->createArrayy($array);
    $first = reset($array);

    if ($first === false) {
      $first = array();
    } else {
      $first = (array)$first;
    }

    self::assertSame($first, $arrayy->firsts()->getArray());
  }

  public function testGetIterator()
  {
    $arrayy = $this->createArrayy();

    self::assertInstanceOf('ArrayIterator', $arrayy->getIterator());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testGetKeys(array $array)
  {
    $arrayy = $this->createArrayy($array);
    $keys = array_keys($array);

    self::assertSame($keys, $arrayy->keys()->getArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testGetRandom(array $array)
  {
    if (0 === count($array)) {
      return;
    }

    $arrayy = $this->createArrayy($array);
    $value = $arrayy->getRandom()->getArray();

    self::assertNotSame(null, $value[0]);
    /** @noinspection TypeUnsafeArraySearchInspection */
    self::assertTrue(in_array($value[0], $arrayy->toArray()));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testGetRandomKey(array $array)
  {
    if (0 === count($array)) {
      return;
    }

    $arrayy = $this->createArrayy($array);
    $key = $arrayy->getRandomKey();

    self::assertNotSame(null, $key);
    self::assertTrue(array_key_exists($key, $arrayy->toArray()));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testGetRandomKeys(array $array)
  {
    if (2 > count($array)) {
      return;
    }

    $arrayy = $this->createArrayy($array);
    $keys = $arrayy->getRandomKeys(2);

    self::assertCount(2, $keys);
    foreach ($keys as $key) {
      self::assertTrue(array_key_exists($key, $array));
    }
  }

  /**
   * @expectedException \RangeException
   */
  public function testGetRandomKeysLogicExceptionGivenNonInteger()
  {
    $arrayy = $this->createArrayy(array('a', 'b', 'c'));
    $arrayy->getRandomKeys('something');
  }

  /**
   * @expectedException \RangeException
   */
  public function testGetRandomKeysLogicExceptionGivenZero()
  {
    $arrayy = $this->createArrayy(array('a', 'b', 'c'));
    $arrayy->getRandomKeys(0);
  }

  /**
   * @expectedException \RangeException
   */
  public function testGetRandomKeysRangeException()
  {
    $arrayy = $this->createArrayy(array('a', 'b', 'c'));
    $arrayy->getRandomKeys(4);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testGetRandomKeysShouldReturnArray(array $array)
  {
    if (0 === count($array)) {
      return;
    }

    $arrayy = $this->createArrayy($array);
    $keys = $arrayy->getRandomKeys(count($array))->getArray();

    self::assertInternalType('array', $keys);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testGetRandomValueSingle(array $array)
  {
    if (0 === count($array)) {
      return;
    }

    $arrayy = $this->createArrayy($array);
    $value = $arrayy->getRandomValue();

    /** @noinspection TypeUnsafeArraySearchInspection */
    self::assertTrue(in_array($value, $array));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testGetRandomValues(array $array)
  {
    if (2 > count($array)) {
      return;
    }

    $arrayy = $this->createArrayy($array);
    $values = $arrayy->getRandomValues(2);

    self::assertCount(2, $values);
    foreach ($values as $value) {
      /** @noinspection TypeUnsafeArraySearchInspection */
      self::assertTrue(in_array($value, $array));
    }
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testGetRandomValuesSingle(array $array)
  {
    if (0 === count($array)) {
      return;
    }

    $arrayy = $this->createArrayy($array);
    $values = $arrayy->getRandomValues(1)->getArray();

    self::assertCount(1, $values);
    self::assertInternalType('array', $values);
    foreach ($values as $value) {
      /** @noinspection TypeUnsafeArraySearchInspection */
      self::assertTrue(in_array($value, $array));
    }
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testIndexOf(array $array)
  {
    $element = 2;

    $arrayy = $this->createArrayy($array);
    $key = array_search($element, $array, true);

    self::assertSame($key, $arrayy->indexOf($element));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array  $array
   * @param string $type
   */
  public function testIsAssoc(array $array, $type = null)
  {
    $arrayy = $this->createArrayy($array);
    $isAssoc = static::TYPE_ASSOC === $type;

    self::assertSame($isAssoc, $arrayy->isAssoc());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testIsEmpty(array $array)
  {
    $isEmpty = !$array;
    $arrayy = $this->createArrayy($array);

    self::assertSame($isEmpty, $arrayy->isEmpty());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array  $array
   * @param string $type
   */
  public function testIsNumeric(array $array, $type = null)
  {
    $arrayy = $this->createArrayy($array);
    $isNumeric = static::TYPE_NUMERIC === $type;

    self::assertSame($isNumeric, $arrayy->isNumeric());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testKey(array $array)
  {
    $arrayy = $this->createArrayy($array);
    $key = key($array);

    self::assertSame($key, key($arrayy->getArray()));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testLast(array $array)
  {
    $arrayy = $this->createArrayy($array);
    $last = end($array);
    $result = $arrayy->last();

    if (empty($array)) {
      $last = null;
    }

    self::assertSame($last, $result);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testNext(array $array)
  {
    $arrayy = $this->createArrayy($array)->getArray();
    $next = next($array);

    self::assertSame($next, next($arrayy));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testOffsetExists(array $array)
  {
    $offset = 1;
    $isOffsetExists = isset($array[$offset]);

    $arrayy = $this->createArrayy($array);

    self::assertSame($isOffsetExists, $arrayy->offsetExists($offset));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testOffsetGet(array $array)
  {
    $offset = 1;
    $value = isset($array[$offset]) ? $array[$offset] : null;

    $arrayy = $this->createArrayy($array);

    self::assertSame($value, $arrayy->offsetGet($offset));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testPrevious(array $array)
  {
    $arrayy = $this->createArrayy($array)->getArray();
    $prev = prev($array);

    self::assertSame($prev, prev($arrayy));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testReIndex(array $array)
  {
    $arrayy = $this->createArrayy($array);
    $values = array_values($array);

    self::assertSame($values, $arrayy->reindex()->getArray());
  }

  public function testReduce()
  {
    $func = function($resultArray, $value) {
      if ($value % 2 === 0) {
        $resultArray[] = $value;
      }

      return $resultArray;
    };
    $array = array(1, 2, 3, 4);
    $arrayy = $this->createArrayy($array);
    $arrayyReduced = $arrayy->reduce($func)->getArray();
    $arrayReduced = (array)array_reduce($array, $func);

    self::assertSame($arrayReduced, $arrayyReduced);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testToJson(array $array)
  {
    $json = json_encode($array);

    $arrayy = $this->createArrayy($array);

    self::assertSame($json, $arrayy->toJson());
  }

  /**
   * @dataProvider stringWithSeparatorProvider
   *
   * @param string $string
   * @param string $separator
   */
  public function testToString($string, $separator)
  {
    $array = explode($separator, $string);

    $arrayy = $this->createArrayy($array);
    $resultString = implode(',', $array);

    self::assertSame($resultString, (string)$arrayy);
    self::assertSame($string, $arrayy->toString($separator));
  }
}
