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
        $this->expectExceptionMessage('The key "unknown" does not exist');

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

    /**
     * Risk: if parseReflectionTypeObject's union-type branch silently accepts every value,
     * only tests that try all three cases (accept A, accept B, reject other) catch the breakage.
     */
    public function testNativeUnionTypeAcceptsBothValidSubtypes()
    {
        $meta = NativeUserData::meta();

        $withInt = new NativeUserData([$meta->id => 1, $meta->firstName => 42]);
        static::assertSame(42, $withInt[$meta->firstName]);

        $withString = new NativeUserData([$meta->id => 1, $meta->firstName => 'Lars']);
        static::assertSame('Lars', $withString[$meta->firstName]);
    }

    /**
     * Risk: if parseReflectionTypeObject returns wrong types for a union, a float would slip through.
     */
    public function testNativeUnionTypeRejectsInvalidType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('#Invalid type: expected "firstName" to be of type \{(int\|string|string\|int)\}#');

        $meta = NativeUserData::meta();
        new NativeUserData([$meta->id => 1, $meta->firstName => 3.14]);
    }

    /**
     * Risk: if typeAllowsNull mis-classifies ?string as non-nullable, null would be rejected;
     * if it mis-classifies as always-nullable, ints would be accepted.
     */
    public function testNativeNullablePropertyRejectsWrongType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('#Invalid type: expected "plz" to be of type \{string\|null\}#');

        $cityMeta = NativeCityData::meta();
        new NativeCityData([
            $cityMeta->name  => 'Düsseldorf',
            $cityMeta->plz   => 999,
            $cityMeta->infos => ['foo'],
        ]);
    }

    /**
     * Risk: if type checking were removed from offsetSet / internalSet, post-construction
     * assignments with wrong types would silently succeed.
     */
    public function testNativePropertyTypeIsEnforcedAfterConstruction()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('#Invalid type: expected "id" to be of type \{int\}#');

        $meta = NativeUserData::meta();
        $model = new NativeUserData([$meta->id => 1, $meta->firstName => 'Lars']);
        $model[$meta->id] = 'not-an-int';
    }

    /**
     * Risk: if checkPropertiesMismatch were removed from checkType, assigning an unknown key
     * after construction on a mismatch-guarded class would silently succeed.
     */
    public function testNativePropertyMismatchIsEnforcedAfterConstruction()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('The key "unknown" does not exist');

        $cityMeta = NativeCityData::meta();
        $model = new NativeCityData([
            $cityMeta->name  => 'Düsseldorf',
            $cityMeta->plz   => null,
            $cityMeta->infos => ['foo'],
        ]);
        $model['unknown'] = 'value';
    }

    /**
     * Risk: if the property-mismatch check in the constructor were bypassed for native classes,
     * omitting required properties would silently succeed.
     */
    public function testNativeMissingRequiredPropertiesInConstructorThrows()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Property mismatch');

        // NativeCityData requires name, plz, infos — omitting plz and infos must throw
        new NativeCityData(['name' => 'Düsseldorf']);
    }

    /**
     * Risk: if the inheritance walk in getPropertiesFromNativeDefinitions stopped at the
     * concrete class, parent-declared properties would never be type-checked in a child.
     */
    public function testNativeInheritedParentPropertyTypeConstraintIsEnforcedInChild()
    {
        $this->expectException(\TypeError::class);
        // NativeBigCityData inherits `string $name` from NativeCityData
        $this->expectExceptionMessageMatches('#Invalid type: expected "name" to be of type \{string\}#');

        $meta = NativeBigCityData::meta();
        new NativeBigCityData([
            $meta->name      => 123,
            $meta->plz       => null,
            $meta->infos     => ['foo'],
            $meta->extraInfo => 'capital',
        ]);
    }

    /**
     * Intersection type syntax `A&B` requires PHP 8.1+.
     *
     * @requires PHP 8.1
     */
    public function testNativeIntersectionTypedPropertiesWork()
    {
        $modelMeta = NativeIntersectionData::meta();
        $items = new \ArrayObject(['foo']);

        $model = new NativeIntersectionData(
            [
                $modelMeta->items => $items,
            ]
        );

        static::assertSame('items', $modelMeta->items);
        static::assertSame($items, $model[$modelMeta->items]);
    }
}
