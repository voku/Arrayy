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
    static::assertEquals('foo bar,UTF-8', $arrayy);
    static::assertEquals('foo bar', $arrayy[0]);
    static::assertEquals('UTF-8', $arrayy[1]);
    static::assertEquals(null, $arrayy[3]);

    foreach ($arrayy as $key => $value) {
      if ($key == 0) {
        static::assertEquals('foo bar', $arrayy[$key]);
      } else if ($key == 1) {
        static::assertEquals('UTF-8', $arrayy[$key]);
      }
    }
  }
}
