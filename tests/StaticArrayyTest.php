<?php

use Arrayy\StaticArrayy as A;

/**
 * Class StaticArrayyTest
 */
class StaticArrayyTest extends PHPUnit_Framework_TestCase
{
  /**
   * @expectedException BadMethodCallException
   */
  public function testBadMethodCall()
  {
    /** @noinspection PhpUndefinedMethodInspection */
    /** @noinspection PhpUnusedLocalVariableInspection */
    /** @noinspection OnlyWritesOnParameterInspection */
    $result = A::invalidMethod('foo');
  }

  public function testEmptyArgsInvocation()
  {
    /** @noinspection PhpUndefinedMethodInspection */
    $result = A::first();
    self::assertNull($result);
  }

  public function testInvocation()
  {
    /** @noinspection PhpUndefinedMethodInspection */
    $result = A::first(array('lall', 'FOOBAR'), 1);
    self::assertSame('lall', $result);
  }

  public function testPartialArgsInvocation()
  {
    /** @noinspection PhpUndefinedMethodInspection */
    $result = A::replaceOneValue(array('foo', 'bar'), 'foo');
    /** @noinspection PhpUndefinedMethodInspection */
    self::assertSame(array('', 'bar'), $result->getArray());
  }

  public function testFullArgsInvocation()
  {
    /** @noinspection PhpUndefinedMethodInspection */
    $result = A::replaceOneValue(array('foo', 'bar'), 'foo', 'test');
    /** @noinspection PhpUndefinedMethodInspection */
    self::assertSame(array('test', 'bar'), $result->getArray());
  }

  public function testArrayyRange()
  {
    $result = A::range(1, null);

    self::assertSame(array(1), $result->getArray());
  }

  public function testArrayyRange1()
  {
    $result = A::range(1, null, 10);

    self::assertSame(array(1), $result->getArray());
  }

  public function testArrayyRange10()
  {
    $result = A::range(1, 10);

    self::assertSame(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), $result->getArray());
  }

  public function testArrayyRange100()
  {
    $result = A::range(0, 100, 10);

    self::assertSame(array(0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100), $result->getArray());
  }

  public function testArrayyRepeat()
  {
    $result = A::repeat('foobar', 3);

    self::assertContains('foobar,foobar,foobar', (string)$result);

    //

    $result = A::repeat('', 3);

    self::assertSame('', (string)$result);
  }

  public function testArrayyRepeatWithArray()
  {
    $result = A::repeat(3, 2);

    self::assertContains('3,3', (string)$result);
  }

  /**
   * Use reflection to ensure that all argument numbers are correct. Each
   * static method should accept 2 more arguments than their Stringy
   * equivalent.
   */
  public function testArgumentNumbers()
  {
    $staticArrayyClass = new ReflectionClass('Arrayy\StaticArrayy');
    $arrayyClass = new ReflectionClass('Arrayy\Arrayy');

    // getStaticPropertyValue can't access protected properties
    $properties = $staticArrayyClass->getStaticProperties();

    foreach ($properties['methodArgs'] as $method => $expected) {
      $num = $arrayyClass->getMethod($method)->getNumberOfParameters() + 2;

      self::assertSame($expected, $num, 'Invalid num args for ' . $method);
    }
  }
}
