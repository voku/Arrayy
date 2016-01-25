<?php

use Arrayy\Arrayy as A;

/**
 * Class ArrayyTestCase
 */
class ArrayyTestCase extends PHPUnit_Framework_TestCase
{
  /**
   * Asserts that a variable is of a Arrayy instance.
   *
   * @param mixed $actual
   */
  public function assertArrayy($actual)
  {
    self::assertInstanceOf('Arrayy\Arrayy', $actual);
  }

  public function testConstruct()
  {
    $arrayy = new A(array('foo bar', 'UTF-8'));
    self::assertArrayy($arrayy);
    self::assertEquals('foo bar,UTF-8', (string)$arrayy);
  }

  public function testEmptyConstruct()
  {
    $arrayy = new A();
    self::assertArrayy($arrayy);
    self::assertEquals('', (string)$arrayy);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testConstructWithArray()
  {
    new A(5);
    static::fail('Expecting exception when the constructor is passed an array');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testMissingToString()
  {
    /** @noinspection PhpExpressionResultUnusedInspection */
    (string)new A(new stdClass());
    static::fail(
        'Expecting exception when the constructor is passed an ' .
        'object without a __toString method'
    );
  }

  /**
   * @dataProvider toStringProvider()
   *
   * @param       $expected
   * @param array $array
   */
  public function testToString($expected, $array)
  {
    self::assertEquals($expected, (string)new A($array));
  }

  /**
   * @return array
   */
  public function toStringProvider()
  {
    return array(
        array('', array(null)),
        array('', array(false)),
        array('1', array(true)),
        array('-9,1,0,', array(-9, 1, 0, false)),
        array('1.18', array(1.18)),
        array(' string  ,foo', array(' string  ', 'foo')),
    );
  }

  /**
   * @dataProvider searchIndexProvider()
   *
   * @param       $expected
   * @param array $array
   * @param mixed $value
   */
  public function testSearchIndex($expected, $array, $value)
  {
    $arrayy = new A($array);

    self::assertEquals($expected, $arrayy->searchIndex($value)->getArray());
  }

  /**
   * @return array
   */
  public function searchIndexProvider()
  {
    return array(
        array(array(), array(null), ''),
        array(array(), array(false), true),
        array(array(0), array(false), false),
        array(array(0), array(true), true),
        array(array(2), array(-9, 1, 0, false), -0),
        array(array(0), array(1.18), 1.18),
        array(array(1), array('string', 'foo'), 'foo'),
    );
  }

  /**
   * @dataProvider searchValueProvider()
   *
   * @param       $expected
   * @param array $array
   * @param mixed $value
   */
  public function testSearchValue($expected, $array, $value)
  {
    $arrayy = new A($array);

    self::assertEquals($expected, $arrayy->searchValue($value)->getArray());
  }

  /**
   * @return array
   */
  public function searchValueProvider()
  {
    return array(
        array(array(), array(null), ''),
        array(array(), array(false), 1),
        array(array(0 => false), array(false), 0),
        array(array(true), array(true), 0),
        array(array(0 => 1), array(-9, 1, 0, false), 1),
        array(array(1.18), array(1.18), 0),
        array(array('foo'), array('string', 'foo'), 1),
    );
  }

  /**
   * @dataProvider matchesProvider()
   *
   * @param $array
   * @param $search
   * @param $result
   */
  public function testMatches($array, $search, $result)
  {
    $arrayy = A::create($array);

    $closure = function ($a) use ($search) {
      return in_array($a, $search, true);
    };

    $resultMatch = $arrayy->matches($closure);

    self::assertEquals($result, $resultMatch);
  }

  /**
   * @return array
   */
  public function matchesProvider()
  {
    return array(
        array(array(), array(null), true),
        array(array(), array(false), true),
        array(array(0 => false), array(false), true),
        array(array(0 => true), array(true), true),
        array(array(0 => -9), array(-9, 1, 0, false), true),
        array(array(0 => -9, 1, 2), array(-9, 1, 0, false), false),
        array(array(1.18), array(1.18), true),
        array(array('string', 'foo', 'lall'), array('string', 'foo'), false),
    );
  }

  /**
   * @dataProvider matchesAnyProvider()
   *
   * @param $array
   * @param $search
   * @param $result
   */
  public function testMatchesAny($array, $search, $result)
  {
    $arrayy = A::create($array);

    $closure = function ($a) use ($search) {
      return in_array($a, $search, true);
    };

    $resultMatch = $arrayy->matchesAny($closure);

    self::assertEquals($result, $resultMatch);
  }

  /**
   * @return array
   */
  public function matchesAnyProvider()
  {
    return array(
        array(array(), array(null), true),
        array(array(), array(false), true),
        array(array(0 => false), array(false), true),
        array(array(0 => true), array(true), true),
        array(array(0 => -9), array(-9, 1, 0, false), true),
        array(array(0 => -9, 1, 2), array(-9, 1, 0, false), true),
        array(array(1.18), array(1.18), true),
        array(array('string', 'foo', 'lall'), array('string', 'foo'), true),
    );
  }

  /**
   * @dataProvider containsProvider()
   *
   * @param array $array
   * @param mixed $value
   * @param       $expected
   */
  public function testContains($array, $value, $expected)
  {
    $arrayy = new A($array);

    self::assertEquals($expected, $arrayy->contains($value));
  }

  /**
   * @return array
   */
  public function containsProvider()
  {
    return array(
        array(array(), null, false),
        array(array(), false, false),
        array(array(0 => false), false, true),
        array(array(0 => true), true, true),
        array(array(0 => -9), -9, true),
        array(array(1.18), 1.18, true),
        array(array(1.18), 1.17, false),
        array(array('string', 'foo'), 'foo', true),
        array(array('string', 'foo123'), 'foo', false),
    );
  }

  /**
   * @dataProvider averageProvider()
   *
   * @param array $array
   * @param mixed $value
   * @param       $expected
   */
  public function testAverage($array, $value, $expected)
  {
    $arrayy = new A($array);

    self::assertEquals($expected, $arrayy->average($value));
  }

  /**
   * @return array
   */
  public function averageProvider()
  {
    return array(
        array(array(), null, 0),
        array(array(), 0, 0),
        array(array(0 => false), false, 0),
        array(array(0 => true), true, 1),
        array(array(0 => -9, -8, -7), 1, -8),
        array(array(0 => -9, -8, -7, 1.32), 2, -5.67),
        array(array(1.18), 1, 1.2),
        array(array(1.18, 1.89), 1, 1.5),
        array(array('string', 'foo'), 1, 0),
        array(array('string', 'foo123'), 'foo', 0),
    );
  }

  /**
   * @dataProvider countProvider()
   *
   * @param array $array
   * @param       $expected
   */
  public function testCount($array, $expected)
  {
    $arrayy = new A($array);

    self::assertEquals($expected, $arrayy->count());
  }

  /**
   * @return array
   */
  public function countProvider()
  {
    return array(
        array(array(), 0),
        array(array(null), 1),
        array(array(0 => false), 1),
        array(array(0 => true), 1),
        array(array(0 => -9, -8, -7), 3),
        array(array(0 => -9, -8, -7, 1.32), 4),
        array(array(1.18), 1),
        array(array(1.18, 1.89), 2),
        array(array('string', 'foo'), 2),
        array(array('string', 'foo123'), 2),
    );
  }

  /**
   * @dataProvider maxProvider()
   *
   * @param array $array
   * @param       $expected
   */
  public function testMax($array, $expected)
  {
    $arrayy = new A($array);

    self::assertEquals($expected, $arrayy->max());
  }

  /**
   * @return array
   */
  public function maxProvider()
  {
    return array(
        array(array(), 0),
        array(array(null), null),
        array(array(0 => false), false),
        array(array(0 => true), 1),
        array(array(0 => -9, -8, -7), -7),
        array(array(0 => -9, -8, -7, 1.32), 1.32),
        array(array(1.18), 1.18),
        array(array(1.18, 1.89), 1.89),
        array(array('string', 'foo'), 'string'),
        array(array('string', 'zoom'), 'zoom'),
    );
  }

  /**
   * @dataProvider minProvider()
   *
   * @param array $array
   * @param       $expected
   */
  public function testMin($array, $expected)
  {
    $arrayy = new A($array);

    self::assertEquals($expected, $arrayy->min());
  }

  /**
   * @return array
   */
  public function minProvider()
  {
    return array(
        array(array(), 0),
        array(array(null), null),
        array(array(0 => false), false),
        array(array(0 => true), 1),
        array(array(0 => -9, -8, -7), -9),
        array(array(0 => -9, -8, -7, 1.32), -9),
        array(array(1.18), 1.18),
        array(array(1.18, 1.89), 1.18),
        array(array('string', 'foo'), 'foo'),
        array(array('string', 'zoom'), 'string'),
    );
  }

  /**
   * @dataProvider findProvider()
   *
   * @param $array
   * @param $search
   * @param $result
   */
  public function testFind($array, $search, $result)
  {
    $arrayy = A::create($array);

    $closure = function ($value) use ($search) {
      return $value === $search;
    };

    $resultMatch = $arrayy->find($closure);

    self::assertEquals($result, $resultMatch);
  }

  /**
   * @return array
   */
  public function findProvider()
  {
    return array(
        array(array(), array(null), false),
        array(array(), array(false), false),
        array(array(0 => true), true, true),
        array(array(0 => -9), -9, true),
        array(array(0 => -9, 1, 2), false, false),
        array(array(1.18), 1.18, true),
        array(array('string', 'foo', 'lall'), 'foo', 'foo'),
    );
  }

  /**
   * @dataProvider cleanProvider()
   *
   * @param $array
   * @param $result
   */
  public function testClean($array, $result)
  {
    $arrayy = A::create($array);

    self::assertEquals($result, $arrayy->clean()->getArray());
  }

  /**
   * @return array
   */
  public function cleanProvider()
  {
    return array(
        array(array(), array()),
        array(array(null, false), array()),
        array(array(0 => true), array(0 => true)),
        array(array(0 => -9, 0), array(true)),
        array(array(-8 => -9, 1, 2 => false), array(0 => 1, -8 => -9)),
        array(array(1.18, false), array(true)),
        array(array('foo' => false, 'foo', 'lall'), array('foo', 'lall')),
    );
  }

  /**
   * @dataProvider randomProvider()
   *
   * @param $array
   */
  public function testRandom($array)
  {
    $arrayy = A::create($array);

    $tmpArray = $arrayy->random()->getArray();

    self::assertEquals(true, in_array($tmpArray[0], $array, true));
  }

  /**
   * @return array
   */
  public function randomProvider()
  {
    return array(
        array(array(0 => true)),
        array(array(0 => -9, 0)),
        array(array(-8 => -9, 1, 2 => false)),
        array(array(1.18, false)),
        array(array('foo' => false, 'foo', 'lall')),
    );
  }


  public function testCanGetIntersectionOfTwoArrays()
  {
    $a = ['foo', 'bar'];
    $b = ['bar', 'baz'];
    $array = A::create($a)->intersection($b);
    self::assertEquals(array('bar'), $array->getArray());
  }

  public function testIntersectsBooleanFlag()
  {
    $a = array('foo', 'bar');
    $b = array('bar', 'baz');
    self::assertTrue(A::create($a)->intersects($b));

    $a = 'bar';
    self::assertTrue(A::create($a)->intersects($b));

    $a = 'foo';
    self::assertFalse(A::create($a)->intersects($b));
  }

  /**
   * @dataProvider firstProvider()
   *
   * @param array $array
   * @param array $result
   * @param null  $take
   */
  public function testFirst($array, $result, $take = null)
  {
    $arrayy = A::create($array);

    self::assertEquals($result, $arrayy->first($take)->getArray());
  }

  /**
   * @return array
   */
  public function firstProvider()
  {
    return array(
        array(array(), array()),
        array(array(null, false), array()),
        array(array(0 => true), array(true)),
        array(array(0 => -9, 0), array(-9)),
        array(array(-8 => -9, 1, 2 => false), array(-9)),
        array(array(1.18, false), array(1.18)),
        array(array('foo' => false, 'foo', 'lall'), array(false)),
        array(array(-8 => -9, 1, 2 => false), array(), 0),
        array(array(1.18, false), array(1.18), 1),
        array(array('foo' => false, 'foo', 'lall'), array('foo', 'foo' => false), 2),
    );
  }

  /**
   * @dataProvider lastProvider()
   *
   * @param array $array
   * @param array $result
   * @param null  $take
   */
  public function testLast($array, $result, $take = null)
  {
    $arrayy = A::create($array);

    self::assertEquals($result, $arrayy->last($take)->getArray());
  }

  /**
   * @return array
   */
  public function lastProvider()
  {
    return array(
        array(array(), array()),
        array(array(null, false), array(false)),
        array(array(0 => true), array(true)),
        array(array(0 => -9, 0), array(0)),
        array(array(-8 => -9, 1, 2 => false), array(false)),
        array(array(1.18, false), array(false)),
        array(array('foo' => false, 'foo', 'lall'), array('lall')),
        array(array(-8 => -9, 1, 2 => false), array(-9, 1, false), 0),
        array(array(1.18, false), array(false), 1),
        array(array('foo' => false, 'foo', 'lall'), array('foo', 'lall'), 2),
    );
  }

  /**
   * @dataProvider initialProvider()
   *
   * @param array $array
   * @param array $result
   * @param int   $to
   */
  public function testInitial($array, $result, $to = 1)
  {
    $arrayy = A::create($array);

    self::assertEquals($result, $arrayy->initial($to)->getArray());
  }

  /**
   * @return array
   */
  public function initialProvider()
  {
    return array(
        array(array(), array()),
        array(array(null, false), array(null)),
        array(array(0 => true), array()),
        array(array(0 => -9, 0), array(-9)),
        array(array(-8 => -9, 1, 2 => false), array(-9, 1)),
        array(array(1.18, false), array(1.18)),
        array(array('foo' => false, 'foo', 'lall'), array('foo' => false, 0 => 'foo')),
        array(array(-8 => -9, 1, 2 => false), array(0 => -9, 1 => 1, 2 => false), 0),
        array(array(1.18, false), array(1.18), 1),
        array(array('foo' => false, 'foo', 'lall'), array('foo' => false), 2),
    );
  }

  /**
   * @dataProvider restProvider()
   *
   * @param array $array
   * @param array $result
   * @param int   $from
   */
  public function testRest($array, $result, $from = 1)
  {
    $arrayy = A::create($array);

    self::assertEquals($result, $arrayy->rest($from)->getArray());
  }

  /**
   * @return array
   */
  public function restProvider()
  {
    return array(
        array(array(), array()),
        array(array(null, false), array(null)),
        array(array(0 => true), array()),
        array(array(0 => -9, 0), array(0)),
        array(array(-8 => -9, 1, 2 => false), array(0 => 1, 1 => false)),
        array(array(1.18, false), array(false)),
        array(array('foo' => false, 'foo', 'lall'), array(0 => 'foo', 1 => 'lall')),
        array(array(-8 => -9, 1, 2 => false), array(0 => -9, 1 => 1, 2 => false), 0),
        array(array(1.18, false), array(false), 1),
        array(array('foo' => false, 'foo', 'lall'), array('lall'), 2),
    );
  }

  public function testCanDoSomethingAtEachValue()
  {
    $arrayy = A::create(array('foo', 'bar' => 'bis'));

    $closure = function ($value, $key) {
      echo $key . ':' . $value . ':';
    };

    $arrayy->at($closure);
    $result = '0:foo:bar:bis:';
    $this->expectOutputString($result);
  }

  public function testReplaceValue()
  {
    $arrayy = A::create(array('bar', 'foo' => 'foo'))->replaceValue('foo', 'replaced');
    self::assertEquals('replaced', $arrayy['foo']);
  }

  public function testReplaceKeys()
  {
    $arrayy = A::create(array(1 => 'bar', 'foo' => 'foo'))->replaceKeys(array(0 => 1, 'foo' => 'replaced'));
    self::assertEquals('bar', $arrayy[1]);
    self::assertEquals('foo', $arrayy['replaced']);
  }

  public function testEach()
  {
    $arrayy = A::create(array(1 => 'bar', 'foo' => 'foo'));

    $closure = function ($value, $key) {
      return $key . ':' . $value;
    };

    $under = $arrayy->each($closure);
    $result = array('foo' => 'foo:foo', 1 => '1:bar');
    self::assertEquals($result, $under);
  }

  public function testShuffle()
  {
    $arrayy = A::create(array(1 => 'bar', 'foo' => 'foo'))->shuffle();

    self::assertEquals(true, in_array('bar', $arrayy->getArray(), true));
    self::assertEquals(true, in_array('foo', $arrayy->getArray(), true));
  }

  /**
   * @dataProvider sortKeysProvider()
   *
   * @param $array
   * @param $result
   * @param $direction
   */
  public function testSortKeys($array, $result, $direction = 'ASC')
  {
    $arrayy = A::create($array)->sortKeys($direction);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function sortKeysProvider()
  {
    return array(
        array(array(), array()),
        array(array(), array()),
        array(array(0 => false), array(false)),
        array(array(0 => true), array(true)),
        array(array(0 => -9), array(-9), 'ASC'),
        array(array(0 => -9, 1, 2), array(-9, 1, 2), 'asc'),
        array(array(1.18), array(1.18), 'ASC'),
        array(array(3 => 'string', 'foo', 'lall'), array(5 => 'lall', 4 => 'foo', 3 => 'string'), 'desc'),
    );
  }

  /**
   * @dataProvider implodeProvider()
   *
   * @param $array
   * @param $result
   * @param $with
   */
  public function testImplode($array, $result, $with = ',')
  {
    $string = A::create($array)->implode($with);

    self::assertEquals($result, $string);
  }

  /**
   * @return array
   */
  public function implodeProvider()
  {
    return array(
        array(array(), ''),
        array(array(), ''),
        array(array(0 => false), ''),
        array(array(0 => true), '1'),
        array(array(0 => -9), '-9', '|'),
        array(array(0 => -9, 1, 2), '-9|1|2', '|'),
        array(array(1.18), '1.18'),
        array(array(3 => 'string', 'foo', 'lall'), 'string,foo,lall', ','),
    );
  }

  public function testFilter()
  {
    $under = A::create(array(1, 2, 3, 4))->filter(
        function ($value) {
          return $value % 2 !== 0;
        }
    );

    self::assertEquals(array(0 => 1, 2 => 3), $under->getArray());
  }

  public function testInvoke()
  {
    $array = array('   foo  ', '   bar   ');
    $arrayy = A::create($array)->invoke('trim');
    self::assertEquals(array('foo', 'bar'), $arrayy->getArray());

    $array = array('_____foo', '____bar   ');
    $arrayy = A::create($array)->invoke('trim', ' _');
    self::assertEquals(array('foo', 'bar'), $arrayy->getArray());

    $array = array('_____foo  ', '__bar   ');
    $arrayy = A::create($array)->invoke('trim', ['_', ' ']);
    self::assertEquals(array('foo  ', '__bar'), $arrayy->getArray());
  }

  public function testReject()
  {
    $array = array(1, 2, 3, 4);
    $arrayy = A::create($array)->reject(
        function ($value) {
          return $value % 2 !== 0;
        }
    );
    self::assertEquals(array(1 => 2, 3 => 4), $arrayy->getArray());
  }

  /**
   * @dataProvider removeFirstProvider()
   *
   * @param $array
   * @param $result
   */
  public function testRemoveFirst($array, $result)
  {
    $arrayy = A::create($array)->removeFirst();

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function removeFirstProvider()
  {
    return array(
        array(array(), array()),
        array(array(), array()),
        array(array(0 => false), array()),
        array(array(0 => true), array()),
        array(array(0 => -9), array()),
        array(array(0 => -9, 1, 2), array(1, 2)),
        array(array(1.18, 1.5), array(1.5)),
        array(array(3 => 'string', 'foo', 'lall'), array('foo', 'lall')),
    );
  }

  /**
   * @dataProvider removeLastProvider()
   *
   * @param $array
   * @param $result
   */
  public function testRemoveLast($array, $result)
  {
    $arrayy = A::create($array)->removeLast();

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function removeLastProvider()
  {
    return array(
        array(array(), array()),
        array(array(0 => false), array()),
        array(array(0 => true), array()),
        array(array(0 => -9), array()),
        array(array(0 => -9, 1, 2), array(-9, 1)),
        array(array(1.18, 1.5), array(1.18)),
        array(array(3 => 'string', 'foo', 'lall'), array(3 => 'string', 4 => 'foo')),
    );
  }

  /**
   * @dataProvider removeValueProvider()
   *
   * @param $array
   * @param $result
   * @param $value
   */
  public function testRemoveValue($array, $result, $value)
  {
    $arrayy = A::create($array)->removeValue($value);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function removeValueProvider()
  {
    return array(
        array(array(), array(), ''),
        array(array(0 => false), array(), false),
        array(array(0 => true), array(), true),
        array(array(0 => -9), array(), -9),
        array(array(0 => -9, 1, 2), array(-9, 1), 2),
        array(array(1.18, 1.5), array(1.18), 1.5),
        array(array(3 => 'string', 'foo', 'lall'), array(0 => 'string', 1 => 'foo'), 'lall'),
    );
  }

  /**
   * @dataProvider prependProvider()
   *
   * @param $array
   * @param $result
   * @param $value
   */
  public function testPrepend($array, $result, $value)
  {
    $arrayy = A::create($array)->prepend($value);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function prependProvider()
  {
    return array(
        array(array(), array('foo'), 'foo'),
        array(array(0 => false), array(true, false), true),
        array(array(0 => true), array(false, true), false),
        array(array(0 => -9), array(-6, -9), -6),
        array(array(0 => -9, 1, 2), array(3, -9, 1, 2), 3),
        array(array(1.18, 1.5), array(1.2, 1.18, 1.5), 1.2),
        array(
            array(3 => 'string', 'foo', 'lall'),
            array(
                0 => 'foobar',
                1 => 'string',
                2 => 'foo',
                3 => 'lall',
            ),
            'foobar',
        ),
    );
  }

  /**
   * @dataProvider appendProvider()
   *
   * @param $array
   * @param $result
   * @param $value
   */
  public function testAppend($array, $result, $value)
  {
    $arrayy = A::create($array)->append($value);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function appendProvider()
  {
    return array(
        array(array(), array('foo'), 'foo'),
        array(array(0 => false), array(false, true), true),
        array(array(0 => true), array(true, false), false),
        array(array(0 => -9), array(-9, -6), -6),
        array(array(0 => -9, 1, 2), array(-9, 1, 2, 3), 3),
        array(array(1.18, 1.5), array(1.18, 1.5, 1.2), 1.2),
        array(array('fòô' => 'bàř'), array('fòô' => 'bàř', 0 => 'foo'), 'foo'),
        array(
            array(3 => 'string', 'foo', 'lall'),
            array(
                3 => 'string',
                4 => 'foo',
                5 => 'lall',
                6 => 'foobar',
            ),
            'foobar',
        ),
    );
  }

  /**
   * @dataProvider uniqueProvider()
   *
   * @param $array
   * @param $result
   */
  public function testUnique($array, $result)
  {
    $arrayy = A::create($array)->unique();

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function uniqueProvider()
  {
    return array(
        array(array(), array()),
        array(array(0 => false), array(false)),
        array(array(0 => true), array(true)),
        array(array(0 => -9, -9), array(-9)),
        array(array(0 => -9, 1, 2), array(-9, 1, 2)),
        array(array(1.18, 1.5), array(1.18, 1.5)),
        array(
            array(3 => 'string', 'foo', 'lall', 'foo'),
            array(
                0 => 'string',
                1 => 'foo',
                2 => 'lall',
            ),
        ),
    );
  }

  /**
   * @dataProvider reverseProvider()
   *
   * @param $array
   * @param $result
   */
  public function testReverse($array, $result)
  {
    $arrayy = A::create($array)->reverse();

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function reverseProvider()
  {
    return array(
        array(array(), array()),
        array(array(0 => false), array(false)),
        array(array(0 => true), array(true)),
        array(array(0 => -9, -9), array(0 => -9, 1 => -9)),
        array(array(0 => -9, 1, 2), array(0 => 2, 1 => 1, 2 => -9)),
        array(array(1.18, 1.5), array(1.5, 1.18)),
        array(
            array(3 => 'string', 'foo', 'lall', 'foo'),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
            ),
        ),
    );
  }
}
