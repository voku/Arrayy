<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;

/**
 * @internal
 */
final class UserDataTest extends \PHPUnit\Framework\TestCase
{
    public function testSetAndGet()
    {
        $cityMeta = CityData::meta();

        $city = new CityData(
            [
                $cityMeta->name  => 'Düsseldorf',
                $cityMeta->plz   => null,
                $cityMeta->infos => ['lall'],
            ]
        );

        // ---

        $modelMeta = UserData::meta();

        $model = new UserData(
            [
                $modelMeta->id        => 1,
                $modelMeta->firstName => 'Lars',
                $modelMeta->lastName  => 'Moelleken',
                $modelMeta->city      => $city,
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
        static::assertSame('{"id":1,"firstName":"Lars","lastName":"Moelleken","city":{"name":"Düsseldorf","plz":null,"infos":["lall"]}}', $model->toJson(\JSON_UNESCAPED_UNICODE));
        static::assertSame('Moelleken', $model['lastName']);
        static::assertSame('Moelleken', $model[$modelMeta->lastName]);
        static::assertSame('Moelleken', $model->lastName);

        static::assertSame('Düsseldorf', $model['city']['name']);
        static::assertSame('Düsseldorf', $model[$modelMeta->city][$cityMeta->name]);
        \assert($model->city instanceof \Arrayy\tests\CityData);
        static::assertSame('Düsseldorf', $model->city->name);

        static::assertNull($model[3]);
    }

    public function testByJsonMapper()
    {
        $json = '{"id":1,"firstName":"Lars","lastName":"Moelleken","city":{"name":"Düsseldorf","plz":null,"infos":["lall"]}}';
        $userData = UserData::createFromJsonMapper($json);

        static::assertInstanceOf(\Arrayy\tests\UserData::class, $userData);
        static::assertSame('Lars', $userData->firstName);
        \assert($userData->city instanceof \Arrayy\tests\CityData);
        static::assertInstanceOf(\Arrayy\tests\CityData::class, $userData->city);
        static::assertSame('Düsseldorf', $userData->city->name);
    }

    /**
     * @depends testSetAndGet
     */
    public function testSetAndGetAgain()
    {
        $modelMeta = UserData::meta();

        $model = new UserData(
            [
                $modelMeta->id        => 2,
                $modelMeta->firstName => 'Lars1',
                $modelMeta->lastName  => 'Moelleken1',
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
        static::assertSame('Moelleken1', $model['lastName']);
        static::assertSame('Moelleken1', $model[$modelMeta->lastName]);
        static::assertNull($model[3]);
    }

    public function testSetFail()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type');

        $modelMeta = UserData::meta();

        $model = new UserData(
            [
                $modelMeta->id => '3',
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
    }

    public function testSetFailObject()
    {
        $this->expectException(\TypeError::class);

        $modelMeta = UserData::meta();

        $model = new UserData(
            [
                $modelMeta->id        => 1,
                $modelMeta->firstName => 'Lars',
                $modelMeta->lastName  => 'Moelleken',
                $modelMeta->city      => (new \stdClass()),
            ]
        );

        static::assertInstanceOf(Arrayy::class, $model);
        static::assertSame('Moelleken', $model['lastName']);
        static::assertSame('Moelleken', $model->lastName);
        static::assertSame('Moelleken', $model[$modelMeta->lastName]);
        static::assertSame('Moelleken', $model::meta()->lastName);
        \assert($model->city instanceof \Arrayy\tests\CityData);
        static::assertSame('Düsseldorf', $model->city->name);
        static::assertSame('Düsseldorf', $model->city::meta()->name);
        static::assertNull($model[3]);
    }
}
