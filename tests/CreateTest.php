<?php

use Stringy\Stringy;

require __DIR__ . '/../src/Create.php';

class CreateTestCase extends PHPUnit_Framework_TestCase
{
  public function testCreate()
  {
    $stringyObject = new Stringy();
    $stringy = $stringyObject->create('foo bar', 'UTF-8');
    $this->assertInstanceOf('Stringy\Stringy', $stringy);
    $this->assertEquals('foo bar', (string)$stringy);
    $this->assertEquals('UTF-8', $stringy->getEncoding());
  }
}
