<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;

/**
 * @internal
 */
final class NativePropertyTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testNativeTypedPropertiesSetAndGet()
    {
        $cityMeta = NativeCityData::meta();
        $city = new NativeCityData(
            [
                $cityMeta->name  => 'Düsseldorf',
                $cityMeta->plz   => null,
                $cityMeta->infos => ['lall'],
            ]
        );

        $modelMeta = NativeUserData::meta();
        $model = new NativeUserData(
            [
                $modelMeta->id        => 1,
                $modelMeta->firstName => 'Lars',
                $modelMeta->lastName  => 'Moelleken',
                $modelMeta->city      => $city,
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
        static::assertSame('Moelleken', $model[$modelMeta->lastName]);
        static::assertSame('Düsseldorf', $model[$modelMeta->city][$cityMeta->name]);
        static::assertSame('city', $modelMeta->city);
    }

    public function testNativeTypedPropertiesRejectInvalidValues()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type');

        $modelMeta = NativeUserData::meta();

        new NativeUserData(
            [
                $modelMeta->id => '3',
            ]
        );
    }

    public function testNativeTypedArrayPropertiesRejectInvalidElementTypes()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected "infos" to be of type {string[]}');

        $cityMeta = NativeCityData::meta();

        new NativeCityData(
            [
                $cityMeta->name  => 'Düsseldorf',
                $cityMeta->plz   => null,
                $cityMeta->infos => [1, 2, 3],
            ]
        );
    }

    public function testNativeMetaIncludesPropertiesDeclaredNatively()
    {
        $cityMeta = NativeCityData::meta();

        static::assertSame('name', $cityMeta->name);
        static::assertSame('plz', $cityMeta->plz);
        static::assertSame('infos', $cityMeta->infos);
    }

    public function testNativeTypedPropertiesRejectUnknownKeysWhenMismatchCheckIsEnabled()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('The key "unknown" does not exists');

        new NativeCityData(
            [
                'name' => 'Düsseldorf',
                'plz' => null,
                'infos' => ['foo'],
                'unknown' => 'value',
            ]
        );
    }

    public function testNativeTypedPropertiesWorkWithJsonMapper()
    {
        $json = '{"id":1,"firstName":"Lars","lastName":"Moelleken","city":{"name":"Düsseldorf","plz":null,"infos":["lall"]}}';
        $userData = NativeUserData::createFromJsonMapper($json);

        static::assertInstanceOf(NativeUserData::class, $userData);
        static::assertSame('Lars', $userData[NativeUserData::meta()->firstName]);
        static::assertInstanceOf(NativeCityData::class, $userData[NativeUserData::meta()->city]);
        static::assertSame('Düsseldorf', $userData[NativeUserData::meta()->city][NativeCityData::meta()->name]);
    }

    public function testNativeTypedArrayPropertiesRejectInvalidJsonMapperElementTypes()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected "infos" to be of type {string[]}');

        NativeCityData::createFromJsonMapper('{"name":"Düsseldorf","plz":null,"infos":[1,2,3]}');
    }

    public function testNativeTypedNestedJsonMapperRejectsInvalidArrayElementTypes()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected "infos" to be of type {string[]}');

        NativeUserData::createFromJsonMapper('{"id":1,"firstName":"Lars","lastName":"Moelleken","city":{"name":"Düsseldorf","plz":null,"infos":[1,2,3]}}');
    }

    public function testNativeTypedPropertiesAreMergedFromParentClasses()
    {
        $modelMeta = NativeBigCityData::meta();
        $model = new NativeBigCityData(
            [
                $modelMeta->name      => 'Düsseldorf',
                $modelMeta->plz       => '40213',
                $modelMeta->infos     => ['foo'],
                $modelMeta->extraInfo => 'capital',
            ]
        );

        static::assertSame('name', $modelMeta->name);
        static::assertSame('extraInfo', $modelMeta->extraInfo);
        static::assertSame('capital', $model[$modelMeta->extraInfo]);
        static::assertSame('40213', $model[$modelMeta->plz]);
    }
}
