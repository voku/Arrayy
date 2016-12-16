<?php

use Arrayy\Arrayy as A;

/**
 * Class ArrayyTestCase
 */
class ArrayyTest extends PHPUnit_Framework_TestCase
{
  const TYPE_ASSOC   = 'assoc';
  const TYPE_EMPTY   = 'empty';
  const TYPE_MIXED   = 'mixed';
  const TYPE_NUMERIC = 'numeric';

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
   * Asserts that a variable is of a Arrayy instance.
   *
   * @param mixed $actual
   */
  public static function assertArrayy($actual)
  {
    self::assertInstanceOf('Arrayy\Arrayy', $actual);
  }

  /**
   * @param A     $arrayzy
   * @param A     $resultArrayzy
   * @param array $array
   * @param array $resultArray
   */
  protected static function assertImmutable(A $arrayzy, A $resultArrayzy, array $array, array $resultArray)
  {
    self::assertNotSame($arrayzy, $resultArrayzy);
    self::assertSame($array, $arrayzy->toArray());
    self::assertSame($resultArray, $resultArrayzy->toArray());
  }

  /**
   * @param A     $arrayzy
   * @param A     $resultArrayzy
   * @param array $resultArray
   */
  protected static function assertMutable(A $arrayzy, A $resultArrayzy, array $resultArray)
  {
    self::assertSame($arrayzy, $resultArrayzy);
    self::assertSame($resultArray, $arrayzy->toArray());
    self::assertSame($resultArray, $resultArrayzy->toArray());
  }

  /**
   * @return array
   */
  public function averageProvider()
  {
    return array(
        array(array(), null, 0),
        array(array(), 0.0, 0),
        array(array(0 => false), false, 0.0),
        array(array(0 => true), true, 1.0),
        array(array(0 => -9, -8, -7), 1, -8.0),
        array(array(0 => -9, -8, -7, 1.32), 2, -5.67),
        array(array(1.18), 1, 1.2),
        array(array(1.18, 1.89), 1, 1.5),
        array(array('string', 'foo'), 1, 0.0),
        array(array('string', 'foo123'), 'foo', 0.0),
    );
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
        array(array(0 => -9, 0), array(0 => -9)),
        array(array(-8 => -9, 1, 2 => false), array(-8 => -9, 0 => 1)),
        array(array(1.18, false), array(0 => 1.18)),
        array(array('foo' => false, 'foo', 'lall'), array('foo', 'lall')),
    );
  }

  /**
   * @return array
   */
  public function containsCaseInsensitiveProvider()
  {
    return array(
        array(array(), null, false),
        array(array(), false, false),
        array(array(0 => false), false, true),
        array(array(0 => true), true, true),
        array(array(0 => -9), -9, true),
        array(array(1.18), 1.18, true),
        array(array(1.18), 1.17, false),
        array(array('string', '💩'), '💩', true),
        array(array(' ', 'É'), 'é', true),
        array(array('string', 'foo'), 'foo', true),
        array(array('string', 'Foo'), 'foo', true),
        array(array('string', 'foo123'), 'foo', false),
        array(array('String', 'foo123'), 'foo', false),
    );
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
                1 => -9,
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
                2     => 'two',
                'foo' => 'bar1',
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
                'foo' => 'bar2',
                3     => 'three',
                4     => 'four',
                6     => 'six',
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

  /**
   * @return array
   */
  public function findProvider()
  {
    return array(
        array(array(), array(null), false),
        array(array(), array(false), false),
        array(array(0 => true), true, true),
        array(array(0 => -9), -9, -9),
        array(array(0 => -9, 1, 2), false, false),
        array(array(1.18), 1.18, 1.18),
        array(array('string', 'foo', 'lall'), 'foo', 'foo'),
    );
  }

  /**
   * @return array
   */
  public function firstProvider()
  {
    return array(
        array(array(), null),
        array(array(null, false), null),
        array(array(0 => true), true),
        array(array(0 => -9, 0), -9),
        array(array(-8 => -9, 1, 2 => false), -9),
        array(array(1.18, false), 1.18),
        array(array('foo' => false, 'foo', 'lall'), false),
        array(array(-8 => -9, 1, 2 => false), -9),
        array(array(1.18, false), 1.18),
        array(array('foo' => false, 'foo', 'lall'), false),
        array(array(2 => 'foo', 3 => 'bar', 4 => 'lall'), 'foo'),
    );
  }

  /**
   * @return array
   */
  public function firstsProvider()
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
        array(array('foo' => false, 'foo', 'lall'), array('foo' => false, 'foo'), 2),
        array(array(2 => 'foo', 3 => 'bar', 4 => 'lall'), array(0 => 'foo', 1 => 'bar'), 2),
    );
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
        array(null, array(' string  ', 'foo'), 'foo'),
        array('foo', array(' string  ', 'foo' => 'foo'), 'foo'),
    );
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
        array(array('foo' => false, 'foo1' => 'lall'), true),
    );
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
        array(array(2 => 'foo', 3 => 'bar', 4 => 'lall'), array(0 => 'lall')),
    );
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
   * @return array
   */
  public function maxProvider()
  {
    return array(
        array(array(), false),
        array(array(null), null),
        array(array(0 => false), false),
        array(array(0 => true), true),
        array(array(0 => -9, -8, -7), -7),
        array(array(0 => -9, -8, -7, 1.32), 1.32),
        array(array(1.18), 1.18),
        array(array(1.18, 1.89), 1.89),
        array(array('string', 'foo'), 'string'),
        array(array('string', 'zoom'), 'zoom'),
    );
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
                2     => 'two',
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
            array(
                3 => 'string',
                4 => 'foo',
                5 => 'lall',
                6 => 'foo',
                0 => 'foo',
                1 => 'lall',
                2 => 'foo',
            ),
        ),
    );
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
                1 => 1,
                2 => 2,
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
                3     => 'three',
                4     => 'four',
                6     => 'six',
                'foo' => 'bar1',
                1     => 'one',
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
                1 => -9,
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
                0     => 'three',
                1     => 'four',
                2     => 'six',
                'foo' => 'bar1',
                3     => 'one',
                4     => 'two',
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
   * @return array
   */
  public function minProvider()
  {
    return array(
        array(array(), false),
        array(array(null), null),
        array(array(0 => false), false),
        array(array(0 => true), true),
        array(array(0 => -9, -8, -7), -9),
        array(array(0 => -9, -8, -7, 1.32), -9),
        array(array(1.18), 1.18),
        array(array(1.18, 1.89), 1.18),
        array(array('string', 'foo'), 'foo'),
        array(array('string', 'zoom'), 'string'),
    );
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

  /**
   * @return array
   */
  public function removeFirstProvider()
  {
    return array(
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

  /**
   * @return array
   */
  public function removeV2Provider()
  {
    return array(
        array(array(), array(), null),
        array(array(0 => false), array(0 => false), false),
        array(array(0 => true), array(0 => true), false),
        array(array(0 => -9), array(0 => -9), -1),
        array(array(0 => -9, 1, 2), array(0 => -9, 2 => 2), 1),
        array(array(1.18, 1.5), array(1 => 1.5), 0),
        array(array(3 => 'string', 'foo', 'lall'), array(3 => 'string', 'foo',), 5),
    );
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
   * @return array
   */
  public function restProvider()
  {
    return array(
        array(array(), array()),
        array(array(null, false), array(false)),
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
   * @return array
   */
  public function searchIndexProvider()
  {
    return array(
        array(false, array(null), ''),
        array(false, array(false), true),
        array(0, array(false), false),
        array(0, array(true), true),
        array(2, array(-9, 1, 0, false), -0),
        array(0, array(1.18), 1.18),
        array(1, array('string', 'foo'), 'foo'),
    );
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
  public function sortKeysProvider()
  {
    return array(
        array(array(), array()),
        array(array(), array()),
        array(array(0 => false), array(false)),
        array(array(0 => true), array(true)),
        array(array(0 => -9), array(-9), 'ASC'),
        array(array(0 => -9, 1, 2), array(-9, 1, 2), 'asc'),
        array(array(1 => 2, 0 => 1), array(1, 2), 'asc'),
        array(array(1.18), array(1.18), 'ASC'),
        array(array(3 => 'string', 'foo', 'lall'), array(5 => 'lall', 4 => 'foo', 3 => 'string'), 'desc'),
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

  public function testAdd()
  {
    $array = array(1, 2);
    $arrayy = new A($array);
    $resultArrayy = $arrayy->add(3);
    $array[] = 3;

    self::assertMutable($arrayy, $resultArrayy, $array);
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

    self::assertSame($result, $arrayy->getArray());
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

    self::assertSame($expected, $arrayy->average($value), 'tested: ' . $value);
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

  public function testCanGetIntersectionOfTwoArrays()
  {
    $a = array('foo', 'bar');
    $b = array('bar', 'baz');
    $array = A::create($a)->intersection($b);
    self::assertSame(array('bar'), $array->getArray());
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
    self::assertSame($matcher, $under->getArray());
  }

  public function testCanGroupValuesWithNonExistingKey()
  {
    self::assertSame(array(), A::create(range(1, 5))->group('unknown', true)->getArray());
    self::assertSame(array(), A::create(range(1, 5))->group('unknown', false)->getArray());
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
    self::assertSame($matcher, $under->getArray());
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
    self::assertSame($expected, A::create($array)->indexBy('age')->getArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testChunk(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->chunk(2);
    $resultArray = array_chunk($array, 2);

    self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);

    // ---

    $arrayy = new A(array(-9, -8, -7, 1.32));
    $result = $arrayy->chunk(2);

    self::assertSame(array(array(-9, -8), array(-7, 1.32)), $result->getArray());
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

    self::assertSame($result, $arrayy->clean()->getArray(), 'tested: ' . print_r($array, true));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testClear(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->clear();

    self::assertMutable($arrayy, $resultArrayy, array());
  }

  public function testColumn()
  {
    $rows = array(0 => array('id' => '3', 'title' => 'Foo', 'date' => '2013-03-25'));

    self::assertEquals(A::create($rows), A::create($rows)->getColumn(null, 0));
    self::assertEquals(A::create($rows), A::create($rows)->getColumn(null));
    self::assertEquals(A::create($rows), A::create($rows)->getColumn());

    $expected = array(
        0 => '3',
    );
    self::assertEquals(A::create($expected), A::create($rows)->getColumn('id'));

    // ---

    $rows = array(
        456 => array('id' => '3', 'title' => 'Foo', 'date' => '2013-03-25'),
        457 => array('id' => '5', 'title' => 'Bar', 'date' => '2012-05-20'),
    );

    $expected = array(
        3 => 'Foo',
        5 => 'Bar',
    );
    self::assertEquals(A::create($expected), A::create($rows)->getColumn('title', 'id'));

    $expected = array(
        0 => 'Foo',
        1 => 'Bar',
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

  public function testConstruct()
  {
    $testArray = array('foo bar', 'UTF-8');
    $arrayy = new A($testArray);
    self::assertArrayy($arrayy);
    self::assertSame('foo bar,UTF-8', (string)$arrayy);
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
   * @dataProvider containsProvider()
   *
   * @param array $array
   * @param mixed $value
   * @param       $expected
   */
  public function testContains($array, $value, $expected)
  {
    $arrayy = new A($array);

    self::assertSame($expected, $arrayy->contains($value));
  }

  /**
   * @dataProvider containsCaseInsensitiveProvider()
   *
   * @param array $array
   * @param mixed $value
   * @param       $expected
   */
  public function testContainsCaseInsensitive($array, $value, $expected)
  {
    $arrayy = new A($array);

    self::assertSame($expected, $arrayy->containsCaseInsensitive($value));
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

    self::assertSame($expected, $arrayy->count());
    self::assertSame($expected, $arrayy->size());
    self::assertSame($expected, $arrayy->length());
  }

  public function testCreateFromJson()
  {
    $str = '
    {"employees":[
      {"firstName":"John", "lastName":"Doe"},
      {"firstName":"Anna", "lastName":"Smith"},
      {"firstName":"Peter", "lastName":"Jones"}
    ]}';

    $arrayy = A::createFromJson($str);

    $expected = array(
        'employees' => array(
            0 => array(
                'firstName' => 'John',
                'lastName'  => 'Doe',
            ),
            1 => array(
                'firstName' => 'Anna',
                'lastName'  => 'Smith',
            ),
            2 => array(
                'firstName' => 'Peter',
                'lastName'  => 'Jones',
            ),
        ),
    );

    // test JSON -> Array
    self::assertSame($expected, $arrayy->getArray());

    // test Array -> JSON
    self::assertSame(
        str_replace(array(' ', "\n", "\n\r", "\r"), '', $str),
        $arrayy->toJson()
    );
  }

  /**
   * @dataProvider stringWithSeparatorProvider
   *
   * @param string $string
   * @param string $separator
   */
  public function testCreateFromString($string, $separator)
  {
    $array = explode($separator, $string);
    $arrayy = new A($array);

    $resultArrayy = A::createFromString($string, $separator);

    self::assertImmutable($arrayy, $resultArrayy, $array, $array);
  }

  public function testCreateFromStringRegEx()
  {
    $str = '
    [2016-03-02 02:37:39] WARN  main : router: error in file-name: jquery.min.map
    [2016-03-02 02:39:07] WARN  main : router: error in file-name: jquery.min.map
    [2016-03-02 02:44:01] WARN  main : router: error in file-name: jquery.min.map
    [2016-03-02 02:45:21] WARN  main : router: error in file-name: jquery.min.map
    ';

    $arrayy = A::createFromString($str, null, '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/');

    $expected = array(
        '[2016-03-02 02:37:39] WARN  main : router: error in file-name: jquery.min.map',
        '[2016-03-02 02:39:07] WARN  main : router: error in file-name: jquery.min.map',
        '[2016-03-02 02:44:01] WARN  main : router: error in file-name: jquery.min.map',
        '[2016-03-02 02:45:21] WARN  main : router: error in file-name: jquery.min.map',
    );

    // test String -> Array
    self::assertSame($expected, $arrayy->getArray());
  }

  public function testCreateFromStringSimple()
  {
    $str = 'John, Doe, Anna, Smith';

    $arrayy = A::createFromString($str, ',');

    $expected = array('John', 'Doe', 'Anna', 'Smith');

    // test String -> Array
    self::assertSame($expected, $arrayy->getArray());
  }

  public function testCreateWithRange()
  {
    $arrayy1 = A::createWithRange(2, 7);
    $array1 = range(2, 7);
    $arrayy2 = A::createWithRange('d', 'h');
    $array2 = range('d', 'h');
    $arrayy3 = A::createWithRange(22, 11, 2);
    $array3 = range(22, 11, 2);
    $arrayy4 = A::createWithRange('y', 'k', 2);
    $array4 = range('y', 'k', 2);

    self::assertSame($array1, $arrayy1->toArray());
    self::assertSame($array2, $arrayy2->toArray());
    self::assertSame($array3, $arrayy3->toArray());
    self::assertSame($array4, $arrayy4->toArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testCustomSort(array $array)
  {
    $callable = function ($a, $b) {
      if ($a == $b) {
        return 0;
      }

      return ($a < $b) ? -1 : 1;
    };

    $arrayy = new A($array);
    $resultArrayy = $arrayy->customSortValues($callable);
    $resultArray = $array;
    usort($resultArray, $callable);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testCustomSortKeys(array $array)
  {
    $callable = function ($a, $b) {
      if ($a == $b) {
        return 0;
      }

      return ($a > $b) ? -1 : 1;
    };

    $arrayy = new A($array);
    $resultArrayy = $arrayy->customSortKeys($callable);
    $resultArray = $array;
    uksort($resultArray, $callable);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  public function testCustomSortKeysSimple()
  {
    $callable = function ($a, $b) {
      if ($a == $b) {
        return 0;
      }

      return ($a > $b) ? 1 : -1;
    };

    $input = array(
        'three' => 3,
        'one'   => 1,
        'two'   => 2,
    );
    $arrayy = new A($input);
    $resultArrayy = $arrayy->customSortKeys($callable);
    $expected = array(
        'one'   => 1,
        'three' => 3,
        'two'   => 2,
    );
    self::assertSame($expected, $resultArrayy->getArray());
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

    self::assertSame($result, $arrayy->getArray(), 'tested: ' . print_r($array, true));
  }

  public function testDiffRecursive()
  {
    $testArray1 = array(
        'test1' => array('lall',),
        'test2' => array('lall',),
    );

    $testArray2 = array(
        'test1' => array('lall',),
        'test2' => array('lall',),
    );

    self::assertEquals(
        new A(array()),
        A::create($testArray1)->diffRecursive($testArray2)
    );

    $testArray1 = array(
        'test1' => array('lall',),
        'test3' => array('lall',),
    );

    $testArray2 = array(
        'test1' => array('lall',),
        'test2' => array('lall',),
    );

    self::assertEquals(
        new A(array('test3' => array('lall',),)),
        A::create($testArray1)->diffRecursive($testArray2)
    );

    $testArray1 = array(
        'test1' => array('lall',),
        'test2' => array('lall',),
    );

    $testArray2 = array(
        'test1' => array('lall',),
        'test2' => array('foo',),
    );

    self::assertEquals(
        new A(array('test2' => array('lall',),)),
        A::create($testArray1)->diffRecursive($testArray2)
    );

    $testArray1 = array(1 => array(1 => 1), 2 => array(2 => 2));
    $testArray2 = array(1 => array(1 => 1));

    self::assertEquals(
        new A(array(2 => array(2 => 2))),
        A::create($testArray1)->diffRecursive($testArray2)
    );
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testDiffWith(array $array)
  {
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($array);
    $resultArrayy = $arrayy->diff($secondArray);
    $resultArray = array_diff($array, $secondArray);

    self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
  }

  public function testDivide()
  {
    $arrayy = new A(array('id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01'));
    $arrayyResult = new A(array('id', 'name', 'group', 'value', 'when', 999, 'flux', '', 6868, '2015-01-01'));

    self::assertSame($arrayyResult->toString(), $arrayy->divide()->toString());
  }

  public function testEach()
  {
    $array = array(1 => 'bar', 'foo' => 'foo');
    $arrayy = A::create($array);

    $closure = function ($value, $key) {
      return $key . ':' . $value;
    };

    $under = $arrayy->each($closure);
    $result = array(1 => '1:bar', 'foo' => 'foo:foo');
    self::assertSame($result, $under->getArray(), 'tested: ' . print_r($array, true));
  }

  public function testEmptyConstruct()
  {
    $arrayy = new A();
    self::assertArrayy($arrayy);
    self::assertSame('', (string)$arrayy);
  }

  public function testFilter()
  {
    $under = A::create(array(1, 2, 3, 4))->filter(
        function ($value) {
          return $value % 2 !== 0;
        }
    );
    self::assertSame(array(0 => 1, 2 => 3), $under->getArray());

    $under = A::create(array(1, 2, 3, 4))->filter();
    self::assertSame(array(1, 2, 3, 4), $under->getArray());
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
    self::assertSame(2365, $b[0]['value']);

    $b = $arrayy->filterBy('name', array('baz'));
    self::assertCount(1, $b);
    /** @noinspection OffsetOperationsInspection */
    self::assertSame(2365, $b[0]['value']);

    $c = $arrayy->filterBy('value', 2468);
    self::assertCount(1, $c);
    /** @noinspection OffsetOperationsInspection */
    self::assertSame('primary', $c[0]['group']);

    $d = $arrayy->filterBy('group', 'primary');
    self::assertCount(3, $d);

    $e = $arrayy->filterBy('value', 2000, 'lt');
    self::assertCount(1, $e);
    /** @noinspection OffsetOperationsInspection */
    self::assertSame(1468, $e[0]['value']);

    $e = $arrayy->filterBy('value', array(2468, 2365), 'contains');
    self::assertCount(2, $e);
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

    self::assertSame($result, $resultMatch, 'tested:' . print_r($array, true));
  }

  /**
   * @dataProvider firstProvider()
   *
   * @param array $array
   * @param array $result
   */
  public function testFirst($array, $result)
  {
    $arrayy = A::create($array);

    self::assertSame($result, $arrayy->first());
  }

  /**
   * @dataProvider firstsProvider()
   *
   * @param array $array
   * @param array $result
   * @param null  $take
   */
  public function testFirsts($array, $result, $take = null)
  {
    $arrayy = A::create($array);
    $resultNew = $arrayy->firstsImmutable($take);
    self::assertSame($result, $resultNew->getArray(), 'tested:' . print_r($array, true));
    self::assertNotSame($arrayy, $resultNew);

    $arrayy = A::create($array);
    $resultNew = $arrayy->firstsMutable($take);
    self::assertSame($result, $resultNew->getArray());
    self::assertSame($arrayy, $resultNew);
  }

  public function testFlip()
  {
    $testArray = array(0 => 'foo', 2 => 'bar', 4 => 'lall');
    $arrayy = A::create($testArray)->flip();

    $expected = array('foo' => 0, 'bar' => 2, 'lall' => 4);
    self::assertSame($expected, $arrayy->getArray());
  }

  public function testForEach()
  {
    $arrayy = new A(array(1 => 'foo bar', 'öäü'));

    foreach ($arrayy as $key => $value) {
      if ($key === 1) {
        self::assertSame('foo bar', $arrayy[$key]);
      } elseif ($key === 2) {
        self::assertSame('öäü', $arrayy[$key]);
      }
    }

  }

  public function testGet()
  {
    $arrayy = new A(array('foo bar', 'öäü'));
    self::assertArrayy($arrayy);
    self::assertSame('öäü', $arrayy[1]);
  }

  /**
   * @dataProvider getProvider()
   *
   * @param mixed $expected
   * @param array $array
   * @param mixed $key
   */
  public function testGetV2($expected, $array, $key)
  {
    $arrayy = new A($array);
    self::assertSame($expected, $arrayy->get($key), 'tested:' . print_r($array, true));
  }

  public function testGetViaDotNotation()
  {
    $arrayy = new A(array('Lars' => array('lastname' => 'Moelleken')));
    $result = $arrayy->get('Lars.lastname');
    self::assertSame('Moelleken', $result);
  }

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
    self::assertSame($expected, $arrayy->has($key));
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

    self::assertSame($result, $string);
  }

  public function testIndexByReturnEmpty()
  {
    $array = array(
        array('name' => 'moe', 'age' => 40),
        array('name' => 'larry', 'age' => 50),
        array('name' => 'curly'),
    );
    self::assertSame(array(), A::create($array)->indexBy('vaaaa')->getArray());
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
    self::assertSame($expected, A::create($array)->indexBy('age')->getArray());
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

    self::assertSame($result, $arrayy->initial($to)->getArray());
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

  public function testInvoke()
  {
    $array = array('   foo  ', '   bar   ');
    $arrayy = A::create($array)->invoke('trim');
    self::assertSame(array('foo', 'bar'), $arrayy->getArray());

    $array = array('_____foo', '____bar   ');
    $arrayy = A::create($array)->invoke('trim', ' _');
    self::assertSame(array('foo', 'bar'), $arrayy->getArray());

    $array = array('_____foo  ', '__bar   ');
    $arrayy = A::create($array)->invoke('trim', array('_', ' '));
    self::assertSame(array('foo  ', '__bar'), $arrayy->getArray());
  }

  public function testIsArrayAssoc()
  {

    $array0 = array(1 => array(1,),);
    $array1 = array(
        1 => 1,
        2 => 2,
    );
    $array2 = array(
        1 => array(1,),
        2 => array(2,),
    );
    $array3 = false;
    $array4 = '';
    $array5 = ' ';
    $array6 = array();
    $array7 = array(
        'test',
        'lall',
    );
    $array8 = array(
        0 => 'test',
        1 => 'lall',
    );
    $array9 = array(
        'lall' => 'test',
        'test' => 'lall',
    );
    $array10 = array('lall' => array('test',),);

    self::assertSame(false, A::create($array0)->isAssoc());
    self::assertSame(false, A::create($array1)->isAssoc());
    self::assertSame(false, A::create($array2)->isAssoc());
    self::assertSame(false, A::create($array3)->isAssoc());
    self::assertSame(false, A::create($array4)->isAssoc());
    self::assertSame(false, A::create($array5)->isAssoc());
    self::assertSame(false, A::create($array6)->isAssoc());
    self::assertSame(false, A::create($array7)->isAssoc());
    self::assertSame(false, A::create($array8)->isAssoc());
    self::assertSame(true, A::create($array9)->isAssoc());
    self::assertSame(true, A::create($array10)->isAssoc());

    // ---

    self::assertTrue(
        A::create(
            array(
                'foo' => 'wibble',
                'bar' => 'wubble',
                'baz' => 'wobble',
            )
        )->isAssoc()
    );

    self::assertFalse(
        A::create(
            array(
                'wibble',
                'wubble',
                'wobble',
            )
        )->isAssoc()
    );
  }

  public function testIsArrayMultidim()
  {
    $testArrays = array(
        array(1 => array(1,),),
        array(0, 1, 2, 3, 4),
        array(
            1 => 1,
            2 => 2,
        ),
        array(
            1 => array(1,),
            2 => array(2,),
        ),
        false,
        '',
        ' ',
        array(),
    );

    $expectedArrays = array(
        true,
        false,
        false,
        true,
        false,
        false,
        false,
        false,
    );

    foreach ($testArrays as $key => $testArray) {
      self::assertSame(
          $expectedArrays[$key], A::create($testArray)
                                  ->isMultiArray(), 'tested:' . print_r($testArray, true)
      );
    }
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

    self::assertSame($result, $resultTmp);
  }

  public function testIsEmpty()
  {
    $testArrays = array(
        array(1 => array(1,),),
        array(0, 1, 2, 3, 4),
        array(
            1 => 1,
            2 => 2,
        ),
        array(
            1 => array(1,),
            2 => array(2,),
        ),
        false,
        '',
        ' ',
        array(),
    );

    $expectedArrays = array(
        false,
        false,
        false,
        false,
        true,
        true,
        false,
        true,
    );

    foreach ($testArrays as $key => $testArray) {
      self::assertSame($expectedArrays[$key], A::create($testArray)->isEmpty(), 'tested:' . print_r($testArray, true));
    }
  }

  public function testIsNumeric()
  {
    $testArrays = array(
        array(1 => array(1,),),
        array(0, 1, 2, 3, 4),
        array(
            1 => 1,
            2 => 2,
        ),
        array(
            1 => array(1,),
            2 => array(2,),
        ),
        false,
        '',
        ' ',
        array(),
    );

    $expectedArrays = array(
        true,
        true,
        true,
        true,
        false,
        false,
        true,
        false,
    );

    foreach ($testArrays as $key => $testArray) {
      self::assertSame(
          $expectedArrays[$key], A::create($testArray)
                                  ->isNumeric(), 'tested:' . print_r($testArray, true)
      );
    }
  }

  public function testIsSequential()
  {
    $testArrays = array(
        array(1 => array(1,),),
        array(0, 1, 2, 3, 4),
        array(
            1 => 1,
            2 => 2,
        ),
        array(
            1 => array(1,),
            2 => array(2,),
        ),
        false,
        '',
        ' ',
        array(),
    );

    $expectedArrays = array(
        false,
        true,
        false,
        false,
        false,
        false,
        true,
        false,
    );

    foreach ($testArrays as $key => $testArray) {
      self::assertSame(
          $expectedArrays[$key], A::create($testArray)
                                  ->isSequential(), 'tested:' . print_r($testArray, true)
      );
    }
  }

  public function testIsSet()
  {
    $arrayy = new A(array('foo bar', 'öäü'));
    self::assertArrayy($arrayy);
    self::assertSame(true, isset($arrayy[0]));
  }

  public function testKeys()
  {
    $arrayyTmp = A::create(array(1 => 'foo', 2 => 'foo2', 3 => 'bar'));
    $keys = $arrayyTmp->keys();

    $matcher = array(1, 2, 3,);
    self::assertSame($matcher, $keys->getArray());
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
    $resultNew = $arrayy->lastsImmutable($take);
    self::assertSame($result, $resultNew->getArray());

    $arrayy = A::create($array);
    $resultNew = $arrayy->lastsMutable($take);
    self::assertSame($result, $resultNew->getArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testMap(array $array)
  {
    $callable = function ($value) {
      return str_repeat($value, 2);
    };
    $arrayy = new A($array);
    $resultArrayy = $arrayy->map($callable);
    $resultArray = array_map($callable, $array);
    self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
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

    self::assertSame($result, $resultMatch);
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

    self::assertSame($result, $resultMatch);
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

    $testArray = array(1, 4, 7);
    $result = A::create($testArray)->matchesAny($closure);
    self::assertSame(true, $result);

    $testArray = array(1, 3, 7);
    $result = A::create($testArray)->matchesAny($closure);
    self::assertSame(false, $result);
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

    $testArray = array(2, 4, 8);
    $result = A::create($testArray)->matches($closure);
    self::assertSame(true, $result);

    $testArray = array(2, 3, 8);
    $result = A::create($testArray)->matches($closure);
    self::assertSame(false, $result);
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

    self::assertSame($expected, $arrayy->max(), 'tested: ' . print_r($array, true));
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

    self::assertSame($result, $arrayy->getArray(), 'tested: ' . print_r($array, true));
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

    self::assertSame($result, $arrayy->getArray());
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

    self::assertSame($result, $arrayy->getArray());
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

    self::assertSame($result, $arrayy->getArray(), 'tested: ' . print_r($array, true));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testMergePrependNewIndexV2(array $array)
  {
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($array);
    $resultArrayy = $arrayy->mergePrependNewIndex($secondArray);
    $resultArray = array_merge($secondArray, $array);

    self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testMergeToRecursively(array $array)
  {
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($array);
    $resultArrayy = $arrayy->mergePrependNewIndex($secondArray, true);
    $resultArray = array_merge_recursive($secondArray, $array);

    self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testMergeWith(array $array)
  {
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($array);
    $resultArrayy = $arrayy->mergeAppendNewIndex($secondArray);
    $resultArray = array_merge($array, $secondArray);

    self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testMergeWithRecursively(array $array)
  {
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($array);
    $resultArrayy = $arrayy->mergeAppendNewIndex($secondArray, true);
    $resultArray = array_merge_recursive($array, $secondArray);

    self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
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

    self::assertSame($expected, $arrayy->min(), 'tested:' . print_r($array, true));
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
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testOffsetNullSet(array $array)
  {
    $offset = null;
    $value = 'new';

    $arrayy = new A($array);
    $arrayy->offsetSet($offset, $value);
    if (isset($offset)) {
      $array[$offset] = $value;
    } else {
      $array[] = $value;
    }

    self::assertSame($array, $arrayy->toArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testOffsetSet(array $array)
  {
    $offset = 1;
    $value = 'new';

    $arrayy = new A($array);
    $arrayy->offsetSet($offset, $value);
    if (isset($offset)) {
      $array[$offset] = $value;
    } else {
      $array[] = $value;
    }

    self::assertSame($array, $arrayy->toArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testOffsetUnset(array $array)
  {
    $arrayy = new A($array);
    $offset = 1;

    $arrayy->offsetUnset($offset);
    unset($array[$offset]);

    self::assertSame($array, $arrayy->toArray());
    self::assertFalse(isset($array[$offset]));
    self::assertFalse($arrayy->offsetExists($offset));
  }

  public function testOffsetUnsetViaDotNotation()
  {
    $array = array('a', 'b' => array(0 => 'c'));
    $arrayy = new A($array);
    $offset = 'b.0';

    self::assertTrue($arrayy->offsetExists($offset));

    $arrayy->offsetUnset($offset);
    unset($array['b'][0]);

    self::assertSame($array, $arrayy->toArray());
    self::assertFalse(isset($array[$offset]));
    self::assertFalse($arrayy->offsetExists($offset));
  }

  public function testOrderByKey()
  {
    $array = array(
        99  => 'aaa',
        100 => 'bcd',
        101 => 123,
        1   => 'Bcde',
        3   => 'bcde',
        4   => 1.1,
        0   => 0,
    );

    // ------

    $arrayy = A::create($array)->sortKeys(SORT_DESC, SORT_REGULAR);
    $result = $arrayy->getArray();

    $expected = array(
        101 => 123,
        100 => 'bcd',
        99  => 'aaa',
        4   => 1.1,
        3   => 'bcde',
        1   => 'Bcde',
        0   => 0,
    );

    self::assertSame($expected, $result);

    // ------

    $arrayy = A::create($array)->sortKeys(SORT_ASC);
    $result = $arrayy->getArray();

    $expected = array(
        0   => 0,
        1   => 'Bcde',
        3   => 'bcde',
        4   => 1.1,
        99  => 'aaa',
        100 => 'bcd',
        101 => 123,
    );

    self::assertSame($expected, $result);
  }

  public function testOrderByValueKeepIndex()
  {
    $array = array(
        100 => 'abc',
        99  => 'aaa',
        2   => 'bcd',
        1   => 'hcd',
        3   => 'bce',
    );

    $arrayy = A::create($array)->sortValueKeepIndex(SORT_DESC);
    $result = $arrayy->getArray();

    $expected = array(
        1   => 'hcd',
        3   => 'bce',
        2   => 'bcd',
        100 => 'abc',
        99  => 'aaa',
    );

    self::assertSame($expected, $result);
  }

  public function testOrderByValueNewIndex()
  {
    $array = array(
        1   => 'hcd',
        3   => 'bce',
        2   => 'bcd',
        100 => 'abc',
        99  => 'aaa',
    );

    $arrayy = A::create($array)->sortValueNewIndex(SORT_ASC, SORT_REGULAR);
    $result = $arrayy->getArray();

    $expected = array(
        0 => 'aaa',
        1 => 'abc',
        2 => 'bcd',
        3 => 'bce',
        4 => 'hcd',
    );

    self::assertSame($expected, $result);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testPad(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->pad(10, 5);
    $resultArray = array_pad($array, 10, 5);

    self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testPop(array $array)
  {
    $arrayy = new A($array);
    $poppedValue = $arrayy->pop();
    $resultArray = $array;
    $poppedArrayValue = array_pop($resultArray);

    self::assertSame($poppedArrayValue, $poppedValue);
    self::assertSame($resultArray, $arrayy->toArray());
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

    self::assertSame($result, $arrayy->getArray());
  }

  public function testPrependKey()
  {
    $arrayy = new A(array('id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01'));
    $arrayyResult = new A(
        array(
            'foo'   => 'lall',
            'id'    => 999,
            'name'  => 'flux',
            'group' => null,
            'value' => 6868,
            'when'  => '2015-01-01',
        )
    );

    self::assertSame($arrayyResult->toString(), $arrayy->prepend('lall', 'foo')->toString());

    // ---

    $arrayy = new A(array('id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01'));
    $arrayyResult = new A(
        array(
            0       => 'lall',
            'id'    => 999,
            'name'  => 'flux',
            'group' => null,
            'value' => 6868,
            'when'  => '2015-01-01',
        )
    );

    self::assertSame($arrayyResult->toString(), $arrayy->prepend('lall')->toString());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testPush(array $array)
  {
    $newElement1 = 5;
    $newElement2 = 10;

    $arrayy = new A($array);
    $resultArrayy = $arrayy->push($newElement1, $newElement2);
    $resultArray = $array;
    array_push($resultArray, $newElement1, $newElement2);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
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
    $result = $arrayy->randomMutable($take)->getArray();

    self::assertSame(true, in_array($result[0], $array, true));
  }

  public function testRandomKey()
  {
    $array = array(1 => 'one', 2 => 'two');
    $arrayy = A::create($array);
    $result = $arrayy->randomKey();

    self::assertSame(true, array_key_exists($result, $array));
  }

  public function testRandomKeys()
  {
    $array = array(1 => 'one', 2 => 'two');
    $arrayy = A::create($array);
    $result = $arrayy->randomKeys(2);

    self::assertSame(true, array_key_exists($result[0], $array));
    self::assertSame(true, array_key_exists($result[1], $array));
  }

  public function testRandomValue()
  {
    $array = array(1 => 'one', 2 => 'two');
    $arrayy = A::create($array);
    $result = $arrayy->randomValue();

    self::assertSame(true, in_array($result, $array, true));
  }

  public function testRandomValues()
  {
    $array = array(1 => 'one', 2 => 'two');
    $arrayy = A::create($array);
    $result = $arrayy->randomValues(2);

    self::assertSame(true, in_array($result[0], $array, true));
    self::assertSame(true, in_array($result[1], $array, true));
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

    self::assertSame(true, in_array($result[0], $array, true));
  }

  public function testReduce()
  {
    $testArray = array('foo', 2 => 'bar', 4 => 'lall');

    $myReducer = function ($resultArray, $value) {
      if ($value === 'foo') {
        $resultArray[] = $value;
      }

      return $resultArray;
    };

    $arrayy = A::create($testArray)->reduce($myReducer);

    $expected = array('foo');
    self::assertSame($expected, $arrayy->getArray());
  }

  public function testReduceViaFunction()
  {
    $testArray = array('foo', 2 => 'bar', 4 => 'lall');

    /**
     * @param $resultArray
     * @param $value
     *
     * @return array
     */
    function myReducer($resultArray, $value)
    {
      if ($value === 'foo') {
        $resultArray[] = $value;
      }

      return $resultArray;
    }

    $arrayy = A::create($testArray)->reduce('myReducer');

    $expected = array('foo');
    self::assertSame($expected, $arrayy->getArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testReindex(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->reindex()->getArray();
    $resultArray = array_values($array);

    self::assertSame(array(), array_diff($resultArrayy, $resultArray));
  }

  public function testReindexSimple()
  {
    $testArray = array(2 => 1, 3 => 2);
    $arrayy = new A($testArray);
    $arrayy->reindex();

    $result = array(0 => 1, 1 => 2);

    self::assertSame($result, $arrayy->getArray());
  }

  public function testReject()
  {
    $array = array(1, 2, 3, 4);
    $arrayy = A::create($array)->reject(
        function ($value) {
          return $value % 2 !== 0;
        }
    );
    self::assertSame(array(1 => 2, 3 => 4), $arrayy->getArray());
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
    $resultTmp = $arrayy->remove($key)->getArray();
    self::assertSame($result, $resultTmp);
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

    self::assertSame($result, $arrayy->getArray());
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

    self::assertSame($result, $arrayy->getArray());
  }

  /**
   * @dataProvider removeV2Provider()
   *
   * @param $array
   * @param $result
   * @param $key
   */
  public function testRemoveV2($array, $result, $key)
  {
    $arrayy = A::create($array)->remove($key);

    self::assertSame($result, $arrayy->getArray());
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

    self::assertSame($result, $arrayy->getArray());
  }

  public function testReplace()
  {
    $arrayyTmp = A::create(array(1 => 'foo', 2 => 'foo2', 3 => 'bar'));

    $arrayy = $arrayyTmp->replace(1, 'notfoo', 'notbar');
    $matcher = array(
        2        => 'foo2',
        3        => 'bar',
        'notfoo' => 'notbar',
    );
    self::assertSame($matcher, $arrayy->getArray());
  }

  public function testReplaceAllKeys()
  {
    $firstArray = array(
        1 => 'one',
        2 => 'two',
        3 => 'three',
    );
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($firstArray);
    $resultArrayy = $arrayy->replaceAllKeys($secondArray)->getArray();
    $resultArray = array_combine($secondArray, $firstArray);

    self::assertSame($resultArray, $resultArrayy);
  }

  public function testReplaceAllKeysV2()
  {
    $firstArray = array(
        1 => 'one',
        2 => 'two',
        3 => 'three',
    );
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($firstArray);
    $resultArrayy = $arrayy->replaceAllKeys($secondArray)->getArray();

    $result = array(
        1     => 'one',
        'one' => 'two',
        2     => 'three',
    );
    self::assertSame($result, $resultArrayy);
  }

  public function testReplaceAllValues()
  {
    $firstArray = array(
        1 => 'one',
        2 => 'two',
        3 => 'three',
    );
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($firstArray);
    $resultArrayy = $arrayy->replaceAllValues($secondArray);
    $resultArray = array_combine($firstArray, $secondArray);

    self::assertImmutable($arrayy, $resultArrayy, $firstArray, $resultArray);
  }

  public function testReplaceAllValuesV2()
  {
    $firstArray = array(
        1 => 'one',
        2 => 'two',
        3 => 'three',
    );
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($firstArray);
    $resultArrayy = $arrayy->replaceAllValues($secondArray);

    $result = array(
        'one'   => 1,
        'two'   => 'one',
        'three' => 2,
    );
    self::assertSame($result, $resultArrayy->getArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testReplaceIn(array $array)
  {
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($array);
    $resultArrayy = $arrayy->mergePrependKeepIndex($secondArray)->getArray();
    $resultArray = array_replace($secondArray, $array);

    self::assertSame(array(), array_diff($resultArrayy, $resultArray));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testReplaceInRecursively(array $array)
  {
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($array);
    $resultArrayy = $arrayy->mergePrependKeepIndex($secondArray, true)->getArray();
    $resultArray = array_replace_recursive($secondArray, $array);

    self::assertSame(array(), array_diff($resultArrayy, $resultArray));
  }

  public function testReplaceKeys()
  {
    $arrayy = A::create(array(1 => 'bar', 'foo' => 'foo'))->replaceKeys(array(1 => 2, 'foo' => 'replaced'));
    self::assertSame('bar', $arrayy[2]);
    self::assertSame('foo', $arrayy['replaced']);

    $arrayy = A::create(array(1 => 'bar', 'foo' => 'foo'))->replaceKeys(array(1, 'foo' => 'replaced'));
    self::assertSame('bar', $arrayy[1]);
    self::assertSame('foo', $arrayy['replaced']);
  }

  public function testReplaceOneValue()
  {
    $testArray = array('bar', 'foo' => 'foo', 'foobar' => 'foobar');
    $arrayy = A::create($testArray)->replaceOneValue('foo', 'replaced');
    self::assertSame('replaced', $arrayy['foo']);
    self::assertSame('foobar', $arrayy['foobar']);
  }

  public function testReplaceV2()
  {
    $arrayyTmp = A::create(array(1 => 'foo', 2 => 'foo2', 3 => 'bar'));

    $arrayy = $arrayyTmp->replace(2, 'notfoo', 'notbar');
    $matcher = array(
        1        => 'foo',
        3        => 'bar',
        'notfoo' => 'notbar',
    );
    self::assertSame($matcher, $arrayy->getArray());
  }

  public function testReplaceValues()
  {
    $testArray = array('bar', 'foo' => 'foo', 'foobar' => 'foobar');
    $arrayy = A::create($testArray)->replaceValues('foo', 'replaced');
    self::assertSame('replaced', $arrayy['foo']);
    self::assertSame('replacedbar', $arrayy['foobar']);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testReplaceWith(array $array)
  {
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($array);
    $resultArrayy = $arrayy->mergeAppendKeepIndex($secondArray)->getArray();
    $resultArray = array_replace($array, $secondArray);

    self::assertSame(array(), array_diff($resultArrayy, $resultArray));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testReplaceWithRecursively(array $array)
  {
    $secondArray = array(
        'one' => 1,
        1     => 'one',
        2     => 2,
    );

    $arrayy = new A($array);
    $resultArrayy = $arrayy->mergeAppendKeepIndex($secondArray, true)->getArray();
    $resultArray = array_replace_recursive($array, $secondArray);

    self::assertSame(array(), array_diff($resultArrayy, $resultArray));
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

    self::assertSame($result, $arrayy->rest($from)->getArray(), 'tested:' . print_r($array, true));
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

    self::assertSame($result, $arrayy->getArray());
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

    self::assertSame($expected, $arrayy->searchIndex($value));
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

    self::assertSame($expected, $arrayy->searchValue($value)->getArray());
  }

  public function testSerialize()
  {
    $testArray = array(1, 4, 7);
    $arrayy = A::create($testArray);
    $result = $arrayy->serialize();

    self::assertSame('a:3:{i:0;i:1;i:1;i:4;i:2;i:7;}', $result);
    self::assertSame('C:13:"Arrayy\Arrayy":30:{a:3:{i:0;i:1;i:1;i:4;i:2;i:7;}}', serialize($arrayy));
    self::assertEquals($arrayy, unserialize('C:13:"Arrayy\Arrayy":30:{a:3:{i:0;i:1;i:1;i:4;i:2;i:7;}}'));

    // create a object with an "arrayy"-property

    $object = new stdClass();
    $object->arrayy = $arrayy;

    self::assertSame($object->arrayy, $arrayy);

    // serialize + tests

    self::assertSame('O:8:"stdClass":1:{s:6:"arrayy";C:13:"Arrayy\Arrayy":30:{a:3:{i:0;i:1;i:1;i:4;i:2;i:7;}}}', serialize($object));
    self::assertEquals($object, unserialize('O:8:"stdClass":1:{s:6:"arrayy";C:13:"Arrayy\Arrayy":30:{a:3:{i:0;i:1;i:1;i:4;i:2;i:7;}}}'));

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
    self::assertSame($value, $arrayy[$key]);
  }

  public function testMagicSetViaDotNotation()
  {
    $arrayy = new A();
    $arrayy['user'] = array('lastname' => 'Moelleken');
    $arrayy['user.firstname'] = 'Lars';

    self::assertSame(array('user' => array('lastname' => 'Moelleken', 'firstname' => 'Lars')), $arrayy->getArray());
    self::assertSame('Lars', $arrayy['user.firstname']);
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
    self::assertSame($value, $result);
  }

  public function testSetAndGetSimple()
  {
    $arrayy = new A(array(1, 2, 3));
    $result = $arrayy->setAndGet(0, 4);

    $expected = 1;
    self::assertSame($expected, $result);

    // ---

    $arrayy = new A(array(1 => 1, 2 => 2, 3 => 3));
    $result = $arrayy->setAndGet(0, 4);

    $expected = 4;
    self::assertSame($expected, $result);
  }

  public function testSetV2()
  {
    $arrayy = new A(array('foo bar', 'UTF-8'));
    $arrayy[1] = 'öäü';
    self::assertArrayy($arrayy);
    self::assertSame('foo bar,öäü', (string)$arrayy);
  }

  public function testSetViaDotNotation()
  {
    $arrayy = new A(array('Lars' => array('lastname' => 'Moelleken')));
    $result = $arrayy->set('Lars.lastname', 'Müller');

    $result = $result->get('Lars.lastname');
    self::assertSame('Müller', $result);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testShift(array $array)
  {
    $arrayy = new A($array);
    $shiftedValue = $arrayy->shift();
    $resultArray = $array;
    $shiftedArrayValue = array_shift($resultArray);

    self::assertSame($shiftedArrayValue, $shiftedValue);
    self::assertSame($resultArray, $arrayy->toArray());
  }

  public function testMoveElement()
  {
    $arr1 = new A(array('a', 'b', 'c', 'd', 'e'));
    $expected = array('a', 'd', 'b', 'c', 'e');
    $newArr1 = $arr1->moveElement(3, 1);

    self::assertSame($expected, $newArr1->toArray());

    // ---

    $arr2 = new A(array('A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e'));
    $expected = array('A' => 'a', 'D' => 'd', 'B' => 'b', 'C' => 'c', 'E' => 'e');
    $newArr2 = $arr2->moveElement('D', 1);

    self::assertSame($expected, $newArr2->toArray());
  }

  public function testShuffle()
  {
    $arrayy = A::create(array(1 => 'bar', 'foo' => 'foo'))->shuffle();

    self::assertSame(true, in_array('bar', $arrayy->getArray(), true));
    self::assertSame(true, in_array('foo', $arrayy->getArray(), true));
  }

  public function testSimpleAt()
  {
    $result = A::create();
    $closure = function ($value, $key) use ($result) {
      $result[$key] = ':' . $value . ':';
    };

    A::create(array('foo', 'bar' => 'bis'))->at($closure);
    self::assertEquals(A::create(array(':foo:', 'bar' => ':bis:')), $result);
  }

  public function testSimpleEach()
  {
    $closure = function ($value) {
      return ':' . $value . ':';
    };

    $result = A::create(array('foo', 'bar' => 'bis'))->each($closure);
    self::assertSame(array(':foo:', 'bar' => ':bis:'), $result->getArray());
  }

  public function testSimpleRandom()
  {
    $testArray = array(-8 => -9, 1, 2 => false);
    $arrayy = A::create($testArray);
    $result = $arrayy->randomMutable(3);
    self::assertSame($arrayy, $result);
    self::assertSame($arrayy, $result);
    self::assertSame(3, count($result));

    $testArray = array(-8 => -9, 1, 2 => false);
    $arrayy = A::create($testArray);
    $result = $arrayy->randomMutable();
    self::assertSame($arrayy, $result);
    self::assertSame($arrayy, $result);
    self::assertSame(1, count($result));

    $testArray = array(-8 => -9, 1, 2 => false);
    $arrayy = A::create($testArray);
    $result = $arrayy->randomImmutable(3);
    self::assertEquals($arrayy, $result);
    self::assertNotSame($arrayy, $result);
    self::assertSame(3, count($result));

    $testArray = array(-8 => -9, 1, 2 => false);
    $arrayy = A::create($testArray);
    $result = $arrayy->randomImmutable();
    self::assertEquals($arrayy, $result);
    self::assertNotSame($arrayy, $result);
    self::assertSame(1, count($result));
  }

  public function testSimpleRandomWeighted()
  {
    $testArray = array('foo', 'bar');
    $result = A::create($testArray)->randomWeighted(array('bar' => 2));
    self::assertSame(1, count($result));

    $testArray = array('foo', 'bar', 'foobar');
    $result = A::create($testArray)->randomWeighted(array('foobar' => 3), 2);
    self::assertSame(2, count($result));
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testSlice(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->slice(1, 1);
    $resultArray = array_slice($array, 1, 1);

    self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
  }

  public function testSort()
  {
    $testArray = array(5, 3, 1, 2, 4);
    $under = A::create($testArray)->sorter(null, 'desc');
    self::assertSame(array(5, 4, 3, 2, 1), $under->getArray());

    $testArray = range(1, 5);
    $under = A::create($testArray)->sorter(
        function ($value) {
          if ($value % 2 === 0) {
            return -1;
          } else {
            return 1;
          }
        }
    );
    self::assertSame(array(2, 4, 1, 3, 5), $under->getArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testSortAscWithPreserveKeys(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->sort(SORT_ASC, SORT_REGULAR, true);
    $resultArray = $array;
    asort($resultArray, SORT_REGULAR);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testSortAscWithoutPreserveKeys(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->sort(SORT_ASC, SORT_REGULAR, false);
    $resultArray = $array;
    sort($resultArray, SORT_REGULAR);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testSortDescWithPreserveKeys(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->sort(SORT_DESC, SORT_REGULAR, true);
    $resultArray = $array;
    arsort($resultArray, SORT_REGULAR);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testSortDescWithoutPreserveKeys(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->sort(SORT_DESC, SORT_REGULAR, false);
    $resultArray = $array;
    rsort($resultArray, SORT_REGULAR);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
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

    self::assertSame($result, $arrayy->getArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testSortKeysAsc(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->sortKeys(SORT_ASC, SORT_REGULAR);
    $resultArray = $array;
    ksort($resultArray, SORT_REGULAR);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testSortKeysDesc(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = $arrayy->sortKeys(SORT_DESC, SORT_REGULAR);
    $resultArray = $array;
    krsort($resultArray, SORT_REGULAR);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  public function testSortV2()
  {
    $array = array(
        1   => 'hcd',
        3   => 'bce',
        2   => 'bcd',
        100 => 'abc',
        99  => 'aaa',
    );

    $arrayy = A::create($array)->sort(SORT_ASC, SORT_REGULAR, false);
    $result = $arrayy->getArray();

    $expected = array(
        0 => 'aaa',
        1 => 'abc',
        2 => 'bcd',
        3 => 'bce',
        4 => 'hcd',
    );

    self::assertSame($expected, $result);
  }

  public function testSplit()
  {
    self::assertArrayy(A::create()->split());

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

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testStaticCreate(array $array)
  {
    $arrayy = new A($array);
    $resultArrayy = A::create($array);

    self::assertImmutable($arrayy, $resultArrayy, $array, $array);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testStaticCreateFromJson(array $array)
  {
    $json = json_encode($array);

    $arrayy = A::create($array);
    $resultArrayy = A::createFromJson($json);

    self::assertImmutable($arrayy, $resultArrayy, $array, $array);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testStaticCreateFromObject(array $array)
  {
    $arrayy = A::create($array);
    $resultArrayy = A::createFromObject($arrayy);

    self::assertImmutable($arrayy, $resultArrayy, $array, $array);
  }

  /**
   * @dataProvider stringWithSeparatorProvider
   *
   * @param string $string
   * @param string $separator
   */
  public function testStaticCreateFromString($string, $separator)
  {
    $array = explode($separator, $string);

    $arrayy = A::create($array);
    $resultArrayy = A::createFromString($string, $separator);

    self::assertImmutable($arrayy, $resultArrayy, $array, $array);
  }

  public function testStripEmpty()
  {
    $arrayy = new A(array('id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01'));
    $arrayyResult = new A(array('id' => 999, 'name' => 'flux', 'value' => 6868, 'when' => '2015-01-01'));

    self::assertSame($arrayyResult->toString(), $arrayy->stripEmpty()->toString());
  }

  public function testSwap()
  {
    $arrayy = new A(array('id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01'));
    $arrayyResult = new A(
        array(
            'id'    => 999,
            'name'  => 'flux',
            'group' => null,
            'value' => '2015-01-01',
            'when'  => 6868,
        )
    );

    self::assertSame($arrayyResult->toString(), $arrayy->swap('value', 'when')->toString());
  }

  /**
   * @dataProvider toStringProvider()
   *
   * @param       $expected
   * @param array $array
   */
  public function testToString($expected, $array)
  {
    self::assertSame($expected, (string)new A($array));
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

    self::assertSame($result, $arrayy->getArray());
  }

  public function testUnserialize()
  {
    $string1 = 'C:13:"Arrayy\Arrayy":30:{a:3:{i:0;i:1;i:1;i:4;i:2;i:7;}}';
    $string2 = 'a:3:{i:0;i:1;i:1;i:4;i:2;i:7;}';

    $testArray = unserialize($string1);
    $arrayy = A::create()->unserialize($string2);

    self::assertSame($string1, serialize($testArray));
    self::assertSame($string2, $arrayy->serialize());
  }

  public function testUnset()
  {
    $arrayy = new A(array('foo bar', 'öäü'));
    unset($arrayy[1]);
    self::assertArrayy($arrayy);
    self::assertSame('foo bar', $arrayy[0]);
    self::assertSame(null, $arrayy[1]);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testUnshift(array $array)
  {
    $newElement1 = 5;
    $newElement2 = 10;

    $arrayy = new A($array);
    $resultArrayy = $arrayy->unshift($newElement1, $newElement2);
    $resultArray = $array;
    array_unshift($resultArray, $newElement1, $newElement2);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  public function testValues()
  {
    $arrayyTmp = A::create(array(1 => 'foo', 2 => 'foo2', 3 => 'bar'));
    $values = $arrayyTmp->values();

    $matcher = array(0 => 'foo', 1 => 'foo2', 2 => 'bar');
    self::assertSame($matcher, $values->getArray());
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testWalk(array $array)
  {
    $callable = function (&$value, $key) {
      $value = $key;
    };

    $arrayy = new A($array);
    $resultArrayy = $arrayy->walk($callable);
    $resultArray = $array;
    array_walk($resultArray, $callable);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  /**
   * @dataProvider simpleArrayProvider
   *
   * @param array $array
   */
  public function testWalkRecursively(array $array)
  {
    $callable = function (&$value, $key) {
      $value = $key;
    };

    $arrayy = new A($array);
    $resultArrayy = $arrayy->walk($callable, true);
    $resultArray = $array;
    array_walk_recursive($resultArray, $callable);

    self::assertMutable($arrayy, $resultArrayy, $resultArray);
  }

  public function testWalkSimple()
  {
    $callable = function (&$value, $key) {
      $value = $key;
    };

    $array = array(1, 2, 3);
    $arrayy = new A($array);
    $resultArrayy = $arrayy->walk($callable);

    $expected = array(0, 1, 2);
    self::assertSame($expected, $resultArrayy->getArray());
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

    self::assertSame($result, $arrayy->getArray(), 'tested:' . print_r($array, true));
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

    self::assertSame($result, $resultTmp);
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
   * @return array
   */
  public function uniqueProvider()
  {
    $a = new stdClass();
    $a->x = 42;

    $b = new stdClass();
    $b->y = 42;

    $c = new stdClass();
    $c->x = 43;

    return array(
        array(array(), array()),
        array(array(0 => false), array(false)),
        array(array(0 => true), array(true)),
        array(array(0 => -9, -9), array(-9)),
        array(array(0 => -9, 1, 2), array(-9, 1, 2)),
        array(array(1.18, 1.5), array(1.18, 1.5)),
        array(
            array(
                3 => 'string',
                'foo',
                'lall',
                'foo',
            ),
            array(
                0 => 'string',
                1 => 'foo',
                2 => 'lall',
            ),
        ),
        array(
            array(
                $a,
                $a,
                $b,
                $b,
                $c,
                $c,
            ),
            array(
                $a,
                $b,
                $c,
            ),
        ),
    );
  }

  public function testContainsValues()
  {
    $this->assertTrue(A::create(array('a', 'b', 'c'))->containsValues(array('a', 'b')));
    $this->assertFalse(A::create(array('a', 'b', 'd'))->containsValues(array('a', 'b', 'c')));
    $this->assertTrue(A::create(array())->containsValues(array()));
    $this->assertTrue(A::create(array('a', 'b', 'c'))->containsValues(array()));
    $this->assertFalse(A::create(array())->containsValues(array('a', 'b', 'c')));
  }

  public function testContainsKeys()
  {
    $this->assertTrue(A::create(array('a' => 0, 'b' => 1, 'c' => 2))->containsKeys(array('a', 'b')));
    $this->assertFalse(A::create(array('a' => 0, 'b' => 1, 'd' => 2))->containsKeys(array('a', 'b', 'c')));
    $this->assertTrue(A::create(array())->containsKeys(array()));
    $this->assertTrue(A::create(array('a' => 0, 'b' => 1, 'c' => 2))->containsKeys(array()));
    $this->assertFalse(A::create(array())->containsKeys(array('a', 'b', 'c')));
  }

}
