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
        static::assertSame('Moelleken', $model['lastName']);
        static::assertSame('Moelleken', $model[$modelMeta->lastName]);
        static::assertSame('Moelleken', $model->lastName);

        static::assertSame('Düsseldorf', $model['city']['name']);
        static::assertSame('Düsseldorf', $model[$modelMeta->city][$cityMeta->name]);
        static::assertSame('Düsseldorf', $model->city->name);

        static::assertNull($model[3]);
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
        $this->expectException(\InvalidArgumentException::class);
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
        $this->expectException(\InvalidArgumentException::class);

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
        static::assertSame('Düsseldorf', $model->city->name);
        static::assertSame('Düsseldorf', $model->city::meta()->name);
        static::assertNull($model[3]);
    }
}
