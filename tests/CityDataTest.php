<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;

require_once __DIR__ . '/CityData.php';

/**
 * @internal
 */
final class CityDataTest extends \PHPUnit\Framework\TestCase
{
    public function testParameterMatchEmpty()
    {
        $model = new CityData(
            []
        );

        static::assertInstanceOf(Arrayy::class, $model);
    }

    public function testParameterMatchFail()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected "infos" to be of type {string[]}, instead got value "foo" (foo) with type {string}.');

        $modelMeta = CityData::meta();

        $model = new CityData(
            [
                $modelMeta->name  => 'Düsseldorf',
                $modelMeta->plz   => null,
                $modelMeta->infos => 'foo',
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
    }

    public function testParameterMatchFailV2()
    {
        $this->expectException(\TypeError::class);

        $modelMeta = CityData::meta();

        $model = new CityData(
            [
                $modelMeta->name  => 'Düsseldorf',
                $modelMeta->plz   => null,
                $modelMeta->infos => [1, 2, 3],
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
    }

    public function testParameterMatchFailWithArray()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Property mismatch');

        $modelMeta = CityData::meta();

        $model = new CityData(
            [
                $modelMeta->name => 'Düsseldorf',
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
    }

    public function testSetAndGet()
    {
        $modelMeta = CityData::meta();

        $model = new CityData(
            [
                $modelMeta->name  => 'Düsseldorf',
                $modelMeta->plz   => null,
                $modelMeta->infos => ['foo', 'bar', 'lall'],
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
        static::assertSame('Düsseldorf', $model['name']);
        static::assertSame('Düsseldorf', $model->name);
        static::assertSame('Düsseldorf', $model[$modelMeta->name]);
        static::assertNull($model[3]);
    }

    public function testDataFromJsonMapper()
    {
        $jsonData = '{"name":"D\u00fcsseldorf","plz":null,"infos":["foo","bar","lall"]}';

        $model = CityData::createFromJsonMapper($jsonData);
        $modelMeta = $model::meta();

        static::assertInstanceOf(Arrayy::class, $model);
        static::assertSame('Düsseldorf', $model['name']);
        static::assertSame('Düsseldorf', $model->name);
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
                $modelMeta->name  => 'Düsseldorf',
                $modelMeta->plz   => null,
                $modelMeta->infos => ['foo'],
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
        static::assertSame('Düsseldorf', $model['name']);
        static::assertSame('Düsseldorf', $model[$modelMeta->name]);
        static::assertNull($model[3]);
    }

    public function testExtendedClass()
    {
        $modelMeta = BigCityData::meta();

        $model = new BigCityData(
            [
                $modelMeta->name       => 'Düsseldorf',
                $modelMeta->plz        => '000000',
                $modelMeta->infos      => ['foo'],
                $modelMeta->extra_info => 'lall',
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
        static::assertSame('lall', $model->extra_info);
        static::assertSame('lall', $model['extra_info']);
        static::assertSame('lall', $model[$modelMeta->extra_info]);
    }

    public function testExtendedClassV2()
    {
        $this->expectExceptionMessageRegExp('#Invalid type: expected "plz" to be of type {string}, instead got value "NULL"#');

        $modelMeta = BigCityData::meta();

        new BigCityData(
            [
                $modelMeta->name       => 'Düsseldorf',
                $modelMeta->plz        => null,
                $modelMeta->infos      => ['foo'],
                $modelMeta->extra_info => 'lall',
            ]
        );
    }
}
