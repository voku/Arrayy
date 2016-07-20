<?php

/**
 * Class CreateTestCase
 */
class CreateTestCase extends PHPUnit_Framework_TestCase
{
  public function testCreate()
  {
    $arrayy = Arrayy\create(array('foo bar', 'UTF-8'));

    static::assertInstanceOf('Arrayy\Arrayy', $arrayy);
    static::assertSame('foo bar,UTF-8', (string)$arrayy);
    static::assertSame('foo bar', $arrayy[0]);
    static::assertSame('UTF-8', $arrayy[1]);
    static::assertSame(null, $arrayy[3]);

    foreach ($arrayy as $key => $value) {
      if ($key == 0) {
        static::assertSame('foo bar', $arrayy[$key]);
      } elseif ($key == 1) {
        static::assertSame('UTF-8', $arrayy[$key]);
      }
    }
  }
}
