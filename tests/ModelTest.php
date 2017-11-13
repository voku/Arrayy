<?php

require_once __DIR__ . '/ModelA.php';
require_once __DIR__ . '/ModelB.php';

/**
 * Class ModelTest
 */
class ModelTest extends \PHPUnit\Framework\TestCase
{
  public function testDotNotation()
  {
    $model = new ModelA(array('foo', 'bar' => array('config' => array('lall' => true))));

    static::assertInstanceOf('Arrayy\Arrayy', $model);
    static::assertSame('foo', $model[0]);
    static::assertSame(true, $model['bar^config^lall']); // the separator was changed in the "ModelA"-class
    static::assertNull($model[3]);
  }

  public function testForEach()
  {
    $colors = new ModelB(array('red', 'yellow', 'green', 'white'));

    foreach ($colors as $key => $color) {
      if ($key == 0) {
        static::assertSame('red', $color);
        break;
      }
    }

    $colors->natsort();


  }
}
