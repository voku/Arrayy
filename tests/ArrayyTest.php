<?php

use Arrayy\Arrayy as A;

/**
 * Class ArrayyTestCase
 */
class ArrayyTest extends PHPUnit_Framework_TestCase
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

  public function testSet()
  {
    $arrayy = new A(array('foo bar', 'UTF-8'));
    $arrayy[1] = 'öäü';
    self::assertArrayy($arrayy);
    self::assertEquals('foo bar,öäü', (string)$arrayy);
  }

  public function testGet()
  {
    $arrayy = new A(array('foo bar', 'öäü'));
    self::assertArrayy($arrayy);
    self::assertEquals('öäü', $arrayy[1]);
  }

  public function testUnset()
  {
    $arrayy = new A(array('foo bar', 'öäü'));
    unset($arrayy[1]);
    self::assertArrayy($arrayy);
    self::assertEquals('foo bar', $arrayy[0]);
    self::assertEquals(null, $arrayy[1]);
  }

  public function testIsSet()
  {
    $arrayy = new A(array('foo bar', 'öäü'));
    self::assertArrayy($arrayy);
    self::assertEquals(true, isset($arrayy[0]));
  }

  public function testForEach()
  {
    $arrayy = new A(array(1 => 'foo bar', 'öäü'));

    foreach ($arrayy as $key => $value) {
      if ($key === 1) {
        self::assertEquals('foo bar', $arrayy[$key]);
      } else if ($key === 2) {
        self::assertEquals('öäü', $arrayy[$key]);
      }
    }

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

  public function testMatchesSimple()
  {
    /** @noinspection PhpUnusedParameterInspection */
    /**
     * @param $value
     * @param $key
     *
     * @return bool
     */
    $closure = function ($value, $key) {
      return ($value % 2 === 0);
    };

    $result = A::create(array(2, 4, 8))->matches($closure);
    self::assertEquals(true, $result);

    $result = A::create(array(2, 3, 8))->matches($closure);
    self::assertEquals(false, $result);
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

  public function testMatchesAnySimple()
  {
    /** @noinspection PhpUnusedParameterInspection */
    /**
     * @param $value
     * @param $key
     *
     * @return bool
     */
    $closure = function ($value, $key) {
      return ($value % 2 === 0);
    };

    $result = A::create(array(1, 4, 7))->matchesAny($closure);
    self::assertEquals(true, $result);

    $result = A::create(array(1, 3, 7))->matchesAny($closure);
    self::assertEquals(false, $result);
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
    self::assertEquals($expected, $arrayy->size());
    self::assertEquals($expected, $arrayy->length());
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
    $closure = function ($value) use ($search) {
      return $value === $search;
    };

    $arrayy = A::create($array);
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

  public function testSimpleRandomWeighted()
  {
    $result = A::create(array('foo', 'bar'))->randomWeighted(array('bar' => 2));
    self::assertEquals(1, count($result));

    $result = A::create(array('foo', 'bar', 'foobar'))->randomWeighted(array('foobar' => 3), 2);
    self::assertEquals(2, count($result));
  }

  /**
   * @dataProvider randomWeightedProvider()
   *
   * @param array $array
   * @param bool  $take
   */
  public function testRandomWeighted($array, $take = null)
  {
    $arrayy = A::create($array);
    $result = $arrayy->randomWeighted(array(0), $take)->getArray();

    self::assertEquals(true, in_array($result[0], $array, true));
  }

  /**
   * @return array
   */
  public function randomWeightedProvider()
  {
    return array(
        array(array(0 => true)),
        array(array(0 => -9, 0)),
        array(array(-8 => -9, 1, 2 => false)),
        array(array(-8 => -9, 1, 2 => false), 2),
        array(array(1.18, false)),
        array(array('foo' => false, 'foo', 'lall')),
        array(array('foo' => false, 'foo', 'lall'), 1),
        array(array('foo' => false, 'foo', 'lall'), 3),
    );
  }

  public function testSimpleRandom()
  {
    $result = A::create(array(-8 => -9, 1, 2 => false))->random(3);
    self::assertEquals(3, count($result));

    $result = A::create(array(-8 => -9, 1, 2 => false))->random();
    self::assertEquals(1, count($result));
  }

  /**
   * @dataProvider randomProvider()
   *
   * @param array $array
   * @param bool  $take
   */
  public function testRandom($array, $take = null)
  {
    $arrayy = A::create($array);
    $result = $arrayy->random($take)->getArray();

    self::assertEquals(true, in_array($result[0], $array, true));
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
        array(array(-8 => -9, 1, 2 => false), 2),
        array(array(1.18, false)),
        array(array('foo' => false, 'foo', 'lall')),
        array(array('foo' => false, 'foo', 'lall'), 1),
        array(array('foo' => false, 'foo', 'lall'), 3),
    );
  }

  /**
   * @dataProvider isAssocProvider()
   *
   * @param array $array
   * @param bool  $result
   */
  public function testIsAssoc($array, $result)
  {
    $resultTmp = A::create($array)->isAssoc();

    self::assertEquals($result, $resultTmp);
  }

  /**
   * @return array
   */
  public function isAssocProvider()
  {
    return array(
        array(array(), false),
        array(array(0 => true), false),
        array(array(0 => -9, 0), false),
        array(array(-8 => -9, 1, 2 => false), false),
        array(array(-8 => -9, 1, 2 => false), false),
        array(array(1.18, false), false),
        array(array(0 => 1, 1 => 2, 2 => 3, 3 => 4), false),
        array(array(1, 2, 3, 4), false),
        array(array(0, 1, 2, 3), false),
        array(array('foo' => false, 'foo', 'lall'), true),
        array(array('foo' => false, 'foo', 'lall'), true),
        array(array('foo' => false, 'foo', 'lall'), true),
    );
  }

  /**
   * @dataProvider isMultiArrayProvider()
   *
   * @param array $array
   * @param bool  $result
   */
  public function testisMultiArray($array, $result)
  {
    $resultTmp = A::create($array)->isMultiArray();

    self::assertEquals($result, $resultTmp);
  }

  /**
   * @return array
   */
  public function isMultiArrayProvider()
  {
    return array(
        array(array(0 => true), false),
        array(array(0 => -9, 0), false),
        array(array(-8 => -9, 1, 2 => false), false),
        array(array(-8 => -9, 1, 2 => false), false),
        array(array(1.18, false), false),
        array(array(0 => 1, 1 => 2, 2 => 3, 3 => 4), false),
        array(array(1, 2, 3, 4), false),
        array(array(0, 1, 2, 3), false),
        array(array('foo' => false, 'foo', 'lall'), false),
        array(array('foo' => false, 'foo', 'lall'), false),
        array(array('foo' => false, 'foo', 'lall'), false),
        array(array('foo' => array('foo', 'lall')), true),
        array(array('foo' => array('foo', 'lall'), 'bar' => array('foo', 'lall')), true),
    );
  }

  public function testSplit()
  {
    self::assertArrayy(A::create(array())->split());

    self::assertEquals(
        A::create(array(array('a'), array('b'))),
        A::create(array('a', 'b'))->split()
    );

    self::assertEquals(
        A::create(array(array('a' => 1), array('b' => 2))),
        A::create(array('a' => 1, 'b' => 2))->split(2, true)
    );

    self::assertEquals(
        A::create(
            array(
                0 => array(
                    0 => 1,
                    1 => 2,
                ),
                1 => array(
                    0 => 3,
                ),
            )
        ),
        A::create(
            array(
                'a' => 1,
                'b' => 2,
                'c' => 3,
            )
        )->split(2, false)
    );
  }

  public function testColumn()
  {
    $rows = array(0 => array('id' => '3', 'title' => 'Foo', 'date' => '2013-03-25'));

    self::assertEquals(A::create($rows), A::create($rows)->getColumn(null, 0));
    self::assertEquals(A::create($rows), A::create($rows)->getColumn(null));
    self::assertEquals(A::create($rows), A::create($rows)->getColumn());

    $expected = array(
        0 => '3'
    );
    self::assertEquals(A::create($expected), A::create($rows)->getColumn('id'));

    // ---

    $rows = array(
        456 => array('id' => '3', 'title' => 'Foo', 'date' => '2013-03-25'),
        457 => array('id' => '5', 'title' => 'Bar', 'date' => '2012-05-20'),
    );

    $expected = array(
        3 => 'Foo',
        5 => 'Bar'
    );
    self::assertEquals(A::create($expected), A::create($rows)->getColumn('title', 'id'));

    $expected = array(
        0 => 'Foo',
        1 => 'Bar'
    );
    self::assertEquals(A::create($expected), A::create($rows)->getColumn('title', null));


    // pass null as second parameter to get back all columns indexed by third parameter
    $expected1 = array(
        3 => array('id' => '3', 'title' => 'Foo', 'date' => '2013-03-25'),
        5 => array('id' => '5', 'title' => 'Bar', 'date' => '2012-05-20'),
    );
    self::assertEquals(A::create($expected1), A::create($rows)->getColumn(null, 'id'));
    
    // pass null as second parameter and bogus third param to get back zero-indexed array of all columns
    $expected2 = array(
        array('id' => '3', 'title' => 'Foo', 'date' => '2013-03-25'),
        array('id' => '5', 'title' => 'Bar', 'date' => '2012-05-20'),
    );
    self::assertEquals(A::create($expected2), A::create($rows)->getColumn(null, 'foo'));

    // pass null as second parameter and no third param to get back array_values(input) (same as $expected2)
    self::assertEquals(A::create($expected2), A::create($rows)->getColumn(null));
  }

  public function testCanGetIntersectionOfTwoArrays()
  {
    $a = array('foo', 'bar');
    $b = array('bar', 'baz');
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
        array(array(2 => 'foo', 3 => 'bar', 4 => 'lall'), array(0 => 'foo', 1 => 'bar'), 2),
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
        array(array(2 => 'foo', 3 => 'bar', 4 => 'lall'), array(0 => 'bar', 1 => 'lall'), 2),
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
        array(array(2 => 'foo', 3 => 'bar', 4 => 'lall'), array(0 => 'foo'), 2),
        array(array(2 => 'foo', 3 => 'bar', 4 => 'lall'), array(0 => 'foo', 1 => 'bar'), 1),
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
        array(array(2 => 'foo', 3 => 'bar', 4 => 'lall'), array(0 => 'lall'), 2),
        array(array(2 => 'foo', 3 => 'bar', 4 => 'lall'), array(0 => 'bar', 1 => 'lall'), 1),
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

  public function testReplaceOneValue()
  {
    $testArray = array('bar', 'foo' => 'foo', 'foobar' => 'foobar');
    $arrayy = A::create($testArray)->replaceOneValue('foo', 'replaced');
    self::assertEquals('replaced', $arrayy['foo']);
    self::assertEquals('foobar', $arrayy['foobar']);
  }

  public function testReplaceValues()
  {
    $testArray = array('bar', 'foo' => 'foo', 'foobar' => 'foobar');
    $arrayy = A::create($testArray)->replaceValues('foo', 'replaced');
    self::assertEquals('replaced', $arrayy['foo']);
    self::assertEquals('replacedbar', $arrayy['foobar']);
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

    $under = A::create(array(1, 2, 3, 4))->filter();
    self::assertEquals(array(1, 2, 3, 4), $under->getArray());
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
    $arrayy = A::create($array)->invoke('trim', array('_', ' '));
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

  /**
   * @dataProvider mergeAppendNewIndexProvider()
   *
   * @param $array
   * @param $arrayNew
   * @param $result
   */
  public function testMergeAppendNewIndex($array, $arrayNew, $result)
  {
    $arrayy = A::create($array)->mergeAppendNewIndex($arrayNew);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function mergeAppendNewIndexProvider()
  {
    return array(
        array(array(), array(), array()),
        array(array(0 => false), array(false), array(false, false)),
        array(array(0 => true), array(true), array(true, true)),
        array(
            array(
                0 => -9,
                -9,
            ),
            array(
                0 => -9,
                1 => -9,
            ),
            array(
                0 => -9,
                1 => -9,
                2 => -9,
                3 => -9,
            ),
        ),
        array(
            array(
                0 => -9,
                1,
                2,
            ),
            array(
                0 => 2,
                1 => 1,
                2 => -9,
            ),
            array(
                0 => -9,
                1 => 1,
                2 => 2,
                3 => 2,
                4 => 1,
                5 => -9,
            ),
        ),
        array(
            array(1.18, 1.5),
            array(1.5, 1.18),
            array(1.18, 1.5, 1.5, 1.18),
        ),
        array(
            array(
                1     => 'one',
                2     => 'two',
                'foo' => 'bar1',
            ),
            array(
                3     => 'three',
                4     => 'four',
                6     => 'six',
                'foo' => 'bar2',
            ),
            array(
                0     => 'one',
                1     => 'two',
                'foo' => 'bar2',
                2     => 'three',
                3     => 'four',
                4     => 'six',
            ),
        ),
        array(
            array(
                3 => 'string',
                'foo',
                'lall',
                'foo',
            ),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
            ),
            array(
                0 => 'string',
                1 => 'foo',
                2 => 'lall',
                3 => 'foo',
                4 => 'foo',
                5 => 'lall',
                6 => 'foo',
                7 => 'string',
            ),
        ),
    );
  }

  /**
   * @dataProvider mergePrependNewIndexProvider()
   *
   * @param $array
   * @param $arrayNew
   * @param $result
   */
  public function testMergePrependNewIndex($array, $arrayNew, $result)
  {
    $arrayy = A::create($array)->mergePrependNewIndex($arrayNew);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function mergePrependNewIndexProvider()
  {
    return array(
        array(array(), array(), array()),
        array(array(0 => false), array(false), array(false, false)),
        array(array(0 => true), array(true), array(true, true)),
        array(
            array(
                0 => -9,
                -9,
            ),
            array(
                0 => -9,
                1 => -9,
            ),
            array(
                0 => -9,
                1 => -9,
                2 => -9,
                3 => -9,
            ),
        ),
        array(
            array(
                0 => -9,
                1,
                2,
            ),
            array(
                0 => 2,
                1 => 1,
                2 => -9,
            ),
            array(
                0 => 2,
                1 => 1,
                2 => -9,
                3 => -9,
                4 => 1,
                5 => 2,
            ),
        ),
        array(
            array(1.18, 1.5),
            array(1.5, 1.18),
            array(1.5, 1.18, 1.18, 1.5),
        ),
        array(
            array(
                1     => 'one',
                2     => 'two',
                'foo' => 'bar1',
            ),
            array(
                3     => 'three',
                4     => 'four',
                6     => 'six',
                'foo' => 'bar2',
            ),
            array(
                1     => 'four',
                'foo' => 'bar1',
                2     => 'six',
                3     => 'one',
                4     => 'two',
                0     => 'three',
            ),
        ),
        array(
            array(
                3 => 'string',
                'foo',
                'lall',
                'foo',
            ),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
            ),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
                4 => 'string',
                5 => 'foo',
                6 => 'lall',
                7 => 'foo',
            ),
        ),
    );
  }

  /**
   * @dataProvider mergeAppendKeepIndexProvider()
   *
   * @param $array
   * @param $arrayNew
   * @param $result
   */
  public function testMergeAppendKeepIndex($array, $arrayNew, $result)
  {
    $arrayy = A::create($array)->mergeAppendKeepIndex($arrayNew);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function mergeAppendKeepIndexProvider()
  {
    return array(
        array(array(), array(), array()),
        array(array(0 => false), array(false), array(false)),
        array(array(0 => true), array(true), array(true)),
        array(
            array(
                0 => -9,
                -9,
            ),
            array(
                0 => -9,
                1 => -9,
            ),
            array(
                0 => -9,
                1 => -9,
            ),
        ),
        array(
            array(
                0 => -9,
                1,
                2,
            ),
            array(
                0 => 2,
                1 => 1,
                2 => -9,
            ),
            array(
                0 => -9,
                1 => 1,
                2 => 2,
            ),
        ),
        array(
            array(1.18, 1.5),
            array(1.5, 1.18),
            array(1.18, 1.5),
        ),
        array(
            array(
                1     => 'one',
                2     => 'two',
                'foo' => 'bar1',
            ),
            array(
                3     => 'three',
                4     => 'four',
                6     => 'six',
                'foo' => 'bar2',
            ),
            array(
                1     => 'one',
                'foo' => 'bar1',
                2     => 'two',
                3     => 'three',
                4     => 'four',
                6     => 'six',
            ),
        ),
        array(
            array(
                3 => 'string',
                'foo',
                'lall',
                'foo',
            ),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
            ),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
                4 => 'foo',
                5 => 'lall',
                6 => 'foo',
            ),
        ),
    );
  }

  /**
   * @dataProvider mergePrependKeepIndexProvider()
   *
   * @param $array
   * @param $arrayNew
   * @param $result
   */
  public function testMergePrependKeepIndex($array, $arrayNew, $result)
  {
    $arrayy = A::create($array)->mergePrependKeepIndex($arrayNew);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function mergePrependKeepIndexProvider()
  {
    return array(
        array(array(), array(), array()),
        array(array(0 => false), array(false), array(false)),
        array(array(0 => true), array(true), array(true)),
        array(
            array(
                0 => -9,
                -9,
            ),
            array(
                0 => -9,
                1 => -9,
            ),
            array(
                0 => -9,
                1 => -9,
            ),
        ),
        array(
            array(
                0 => -9,
                1,
                2,
            ),
            array(
                0 => 2,
                1 => 1,
                2 => -9,
            ),
            array(
                0 => 2,
                1 => 1,
                2 => -9,
            ),
        ),
        array(
            array(1.18, 1.5),
            array(1.5, 1.18),
            array(1.5, 1.18),
        ),
        array(
            array(
                1     => 'one',
                2     => 'two',
                'foo' => 'bar1',
            ),
            array(
                3     => 'three',
                4     => 'four',
                6     => 'six',
                'foo' => 'bar2',
            ),
            array(
                1     => 'one',
                'foo' => 'bar2',
                2     => 'two',
                3     => 'three',
                4     => 'four',
                6     => 'six',
            ),
        ),
        array(
            array(
                3 => 'string',
                'foo',
                'lall',
                'foo',
            ),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
            ),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
                4 => 'foo',
                5 => 'lall',
                6 => 'foo',
            ),
        ),
    );
  }

  /**
   * @dataProvider diffProvider()
   *
   * @param $array
   * @param $arrayNew
   * @param $result
   */
  public function testDiff($array, $arrayNew, $result)
  {
    $arrayy = A::create($array)->diff($arrayNew);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function diffProvider()
  {
    return array(
        array(array(), array(), array()),
        array(array(0 => false), array(false), array()),
        array(array(0 => true), array(true), array()),
        array(
            array(
                0 => -9,
                -9,
            ),
            array(
                0 => -9,
                1 => -9,
            ),
            array(),
        ),
        array(
            array(
                0 => -9,
                1,
                2,
            ),
            array(
                0 => 2,
                1 => 1,
                2 => -9,
            ),
            array(),
        ),
        array(
            array(1.18, 1.5),
            array(1.5, 1.18),
            array(),
        ),
        array(
            array(
                1     => 'one',
                2     => 'two',
                'foo' => 'bar1',
            ),
            array(
                3     => 'three',
                4     => 'four',
                6     => 'six',
                'foo' => 'bar2',
            ),
            array(
                1     => 'one',
                'foo' => 'bar1',
                2     => 'two',
            ),
        ),
        array(
            array(
                3 => 'string',
                'foo',
                'lall',
                'foo',
            ),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
            ),
            array(),
        ),
    );
  }

  /**
   * @dataProvider diffReverseProvider()
   *
   * @param $array
   * @param $arrayNew
   * @param $result
   */
  public function testdiffReverse($array, $arrayNew, $result)
  {
    $arrayy = A::create($array)->diffReverse($arrayNew);

    self::assertEquals($result, $arrayy->getArray());
  }

  /**
   * @return array
   */
  public function diffReverseProvider()
  {
    return array(
        array(array(), array(), array()),
        array(array(0 => false), array(false), array()),
        array(array(0 => true), array(true), array()),
        array(
            array(
                0 => -9,
                -9,
            ),
            array(
                0 => -9,
                1 => -9,
            ),
            array(),
        ),
        array(
            array(
                0 => -9,
                1,
                2,
            ),
            array(
                0 => 2,
                1 => 1,
                2 => -9,
            ),
            array(),
        ),
        array(
            array(1.18, 1.5),
            array(1.5, 1.18),
            array(),
        ),
        array(
            array(
                1     => 'one',
                2     => 'two',
                'foo' => 'bar1',
            ),
            array(
                3     => 'three',
                4     => 'four',
                6     => 'six',
                'foo' => 'bar2',
            ),
            array(
                'foo' => 'bar2',
                3     => 'three',
                4     => 'four',
                6     => 'six',
            ),
        ),
        array(
            array(
                3 => 'string',
                'foo',
                'lall',
                'foo',
            ),
            array(
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
                3 => 'string',
            ),
            array(),
        ),
    );
  }
}
