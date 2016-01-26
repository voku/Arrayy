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

  /**
   * @dataProvider setAndGetProvider()
   *
   * @param array $array
   * @param mixed $key
   * @param mixed $value
   */
  public function testSetAndGet($array, $key, $value)
  {
    $arrayy = new A($array);
    $result = $arrayy->setAndGet($key, $value);
    self::assertEquals($value, $result);
  }

  /**
   * @return array
   */
  public function setAndGetProvider()
  {
    return array(
        array(array(null), 0, 'foo'),
        array(array(false), 0, false),
        array(array(true), 1, 'foo'),
        array(array(false), 1, 'foo'),
        array(array(true), 0, true),
        array(array(-9, 1, 0, false), 1, 1),
        array(array(1.18), 0, 1.18),
        array(array(' string  ', 'foo'), 'foo', 'lall'),
        array(array(' string  ', 'foo' => 'foo'), 'foo', 'foo'),
    );
  }

  /**
   * @dataProvider removeProvider()
   *
   * @param array $array
   * @param mixed $key
   * @param array $result
   */
  public function testRemove($array, $key, $result)
  {
    $arrayy = new A($array);
    $resultTmp = $arrayy->remove($key);
    self::assertEquals($result, $resultTmp);
  }

  /**
   * @return array
   */
  public function removeProvider()
  {
    return array(
        array(array(null), 0, array()),
        array(array(false), 0, array()),
        array(array(true), 1, array(true)),
        array(array(false), 1, array(false)),
        array(array(true), 0, array()),
        array(array(-9, 1, 0, false), 1, array(0 => -9, 2 => 0, 3 => false)),
        array(array(1.18), 0, array()),
        array(array(' string  ', 'foo'), 'foo', array(' string  ', 'foo')),
        array(array(' string  ', 'foo' => 'foo'), 'foo', array(' string  ')),
    );
  }

  public function testFilterBy()
  {
    $a = array(
        array('id' => 123, 'name' => 'foo', 'group' => 'primary', 'value' => 123456, 'when' => '2014-01-01'),
        array('id' => 456, 'name' => 'bar', 'group' => 'primary', 'value' => 1468, 'when' => '2014-07-15'),
        array('id' => 499, 'name' => 'baz', 'group' => 'secondary', 'value' => 2365, 'when' => '2014-08-23'),
        array('id' => 789, 'name' => 'ter', 'group' => 'primary', 'value' => 2468, 'when' => '2010-03-01'),
        array('id' => 888, 'name' => 'qux', 'value' => 6868, 'when' => '2015-01-01'),
        array('id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01'),
    );

    $arrayy = new A($a);

    $b = $arrayy->filterBy('name', 'baz');
    self::assertCount(1, $b);
    /** @noinspection OffsetOperationsInspection */
    self::assertEquals(2365, $b[0]['value']);

    $b = $arrayy->filterBy('name', array('baz'));
    self::assertCount(1, $b);
    /** @noinspection OffsetOperationsInspection */
    self::assertEquals(2365, $b[0]['value']);

    $c = $arrayy->filterBy('value', 2468);
    self::assertCount(1, $c);
    /** @noinspection OffsetOperationsInspection */
    self::assertEquals('primary', $c[0]['group']);

    $d = $arrayy->filterBy('group', 'primary');
    self::assertCount(3, $d);

    $e = $arrayy->filterBy('value', 2000, 'lt');
    self::assertCount(1, $e);
    /** @noinspection OffsetOperationsInspection */
    self::assertEquals(1468, $e[0]['value']);

    $e = $arrayy->filterBy('value', array(2468, 2365), 'contains');
    self::assertCount(2, $e);
  }

  public function testReplace()
  {
    $arrayyTmp = A::create(array(1 => 'foo', 2 => 'foo2', 3 => 'bar'));
    $arrayy = $arrayyTmp->replace(1, 'notfoo', 'notbar');

    $matcher = array(
        'notfoo' => 'notbar',
        2        => 'foo2',
        3        => 'bar',
    );
    self::assertEquals($matcher, $arrayy->getArray());
  }

  public function testKeys()
  {
    $arrayyTmp = A::create(array(1 => 'foo', 2 => 'foo2', 3 => 'bar'));
    $keys = $arrayyTmp->keys();

    $matcher = array(1, 2, 3,);
    self::assertEquals($matcher, $keys->getArray());
  }

  public function testValues()
  {
    $arrayyTmp = A::create(array(1 => 'foo', 2 => 'foo2', 3 => 'bar'));
    $values = $arrayyTmp->values();

    $matcher = array(0 => 'foo', 1 => 'foo2', 2 => 'bar');
    self::assertEquals($matcher, $values->getArray());
  }

  public function testSort()
  {
    $testArray = array(5, 3, 1, 2, 4);
    $under = A::create($testArray)->sort(null, 'desc');
    self::assertEquals(array(5, 4, 3, 2, 1), $under->getArray());

    $testArray = range(1, 5);
    $under = A::create($testArray)->sort(
        function ($value) {
          return $value % 2 === 0;
        }
    );
    self::assertEquals(array(1, 3, 5, 2, 4), $under->getArray());
  }

  public function testCanGroupValues()
  {
    $under = A::create(range(1, 5))->group(
        function ($value) {
          return $value % 2 === 0;
        }
    );
    $matcher = array(
        array(1, 3, 5),
        array(2, 4),
    );
    self::assertEquals($matcher, $under->getArray());
  }

  public function testCanGroupValuesWithSavingKeys()
  {
    $grouper = function ($value) {
      return $value % 2 === 0;
    };
    $under = A::create(range(1, 5))->group($grouper, true);
    $matcher = array(
        array(0 => 1, 2 => 3, 4 => 5),
        array(1 => 2, 3 => 4),
    );
    self::assertEquals($matcher, $under->getArray());
  }

  public function testCanGroupValuesWithNonExistingKey()
  {
    self::assertEquals([], A::create(range(1, 5))->group('unknown', true)->getArray());
    self::assertEquals([], A::create(range(1, 5))->group('unknown', false)->getArray());
  }

  public function testCanIndexBy()
  {
    $array = array(
        array('name' => 'moe', 'age' => 40),
        array('name' => 'larry', 'age' => 50),
        array('name' => 'curly', 'age' => 60),
    );
    $expected = array(
        40 => array('name' => 'moe', 'age' => 40),
        50 => array('name' => 'larry', 'age' => 50),
        60 => array('name' => 'curly', 'age' => 60),
    );
    self::assertEquals($expected, A::create($array)->indexBy('age')->getArray());
  }

  public function testIndexByReturnSome()
  {
    $array = array(
        array('name' => 'moe', 'age' => 40),
        array('name' => 'larry', 'age' => 50),
        array('name' => 'curly'),
    );
    $expected = array(
        40 => array('name' => 'moe', 'age' => 40),
        50 => array('name' => 'larry', 'age' => 50),
    );
    self::assertEquals($expected, A::create($array)->indexBy('age')->getArray());
  }

  public function testIndexByReturnEmpty()
  {
    $array = array(
        array('name' => 'moe', 'age' => 40),
        array('name' => 'larry', 'age' => 50),
        array('name' => 'curly'),
    );
    self::assertEquals([], A::create($array)->indexBy('vaaaa')->getArray());
  }
}
