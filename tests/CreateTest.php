<?php

use Arrayy\Arrayy;

/**
 * Class CreateTestCase
 */
class CreateTestCase extends PHPUnit_Framework_TestCase
{
  public function testCreate()
  {
    $arrayyObject = new Arrayy();
    $arrayy = $arrayyObject::create(array('foo bar', 'UTF-8'));
    static::assertInstanceOf('Arrayy\Arrayy', $arrayy);
    static::assertEquals('foo bar,UTF-8', $arrayy);
  }
}
