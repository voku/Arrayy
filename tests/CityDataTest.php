<?php

use Arrayy\Arrayy;

require_once __DIR__ . '/CityData.php';

/**
 * Class CityDataTest
 */
class CityDataTest extends \PHPUnit\Framework\TestCase
{
  public function testSetAndGet()
  {
    $modelMeta = CityData::meta();

    $model = new CityData(
        [
            $modelMeta->name => 'Düsseldorf',
            $modelMeta->plz  => null,
        ]
    );

    static::assertInstanceOf(Arrayy::class, $model);
    static::assertSame('Düsseldorf', $model['name']);
    static::assertSame('Düsseldorf', $model[$modelMeta->name]);
    static::assertNull($model[3]);
  }

  /**
   * @depends testSetAndGet
   */
  public function testSetAndGetAgain()
  {
    $modelMeta = CityData::meta();

    $model = new CityData(
        [
            $modelMeta->name => 'Düsseldorf',
            $modelMeta->plz  => null,
        ]
    );

    static::assertInstanceOf(Arrayy::class, $model);
    static::assertSame('Düsseldorf', $model['name']);
    static::assertSame('Düsseldorf', $model[$modelMeta->name]);
    static::assertNull($model[3]);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Property mismatch
   */
  public function testParameterMatchFail()
  {
    $modelMeta = CityData::meta();

    $model = new CityData(
        [
            $modelMeta->name => 'Düsseldorf',
        ]
    );

    static::assertInstanceOf(Arrayy::class, $model);
  }

  public function testParameterMatchEmpty()
  {
    $model = new CityData(
        []
    );

    static::assertInstanceOf(Arrayy::class, $model);
  }
}
