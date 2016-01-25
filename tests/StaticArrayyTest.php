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
    $result = A::invalidMethod('foo');
  }

  public function testEmptyArgsInvocation()
  {
    $result = A::first();
    self::assertEquals('', $result);
  }

  public function testInvocation()
  {
    $result = A::first(array('lall', 'FOOBAR'), 1);
    self::assertEquals('lall', $result);
  }

  public function testPartialArgsInvocation()
  {
    $result = A::replaceOneValue(array('foo', 'bar'), 'foo');
    self::assertEquals(array('', 'bar'), $result->getArray());
  }

  public function testFullArgsInvocation()
  {
    $result = A::replaceOneValue(array('foo', 'bar'), 'foo', 'test');
    self::assertEquals(array('test', 'bar'), $result->getArray());
  }

  public function testArrayyRange()
  {
    $result = A::range(1, null);

    self::assertEquals(array(1), $result->getArray());
  }

  public function testArrayyRange1()
  {
    $result = A::range(1, null, 10);

    self::assertEquals(array(1), $result->getArray());
  }

  public function testArrayyRange10()
  {
    $result = A::range(1, 10);

    self::assertEquals(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), $result->getArray());
  }

  public function testArrayyRange100()
  {
    $result = A::range(0, 100, 10);

    self::assertEquals(array(0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100), $result->getArray());
  }

  public function testArrayyRepeat()
  {
    $result = A::repeat('foobar', 3);

    self::assertContains('foobar,foobar,foobar', (string)$result);
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

      self::assertEquals($expected, $num, 'Invalid num args for ' . $method);
    }
  }
}
