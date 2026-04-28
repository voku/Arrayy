<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\tests\PHPStan\ArrayShapeCity;
use Arrayy\tests\PHPStan\ArrayShapeUser;

/**
 * @internal
 */
final class MetaRuntimeTest extends \PHPUnit\Framework\TestCase
{
    public function testArrayShapeMetaSupportsNestedRuntimeAccess(): void
    {
        $cityMeta = ArrayShapeCity::meta();
        $city = new ArrayShapeCity([
            $cityMeta->name => 'Düsseldorf',
            $cityMeta->plz  => null,
        ]);

        $userMeta = ArrayShapeUser::meta();
        $user = new ArrayShapeUser([
            $userMeta->id        => 1,
            $userMeta->firstName => 'Lars',
            $userMeta->lastName  => 'Moelleken',
            $userMeta->city      => $city,
        ]);

        static::assertSame('id', $userMeta->id);
        static::assertSame('city', $userMeta->city);
        static::assertSame('name', $cityMeta->name);
        static::assertSame(1, $user[$userMeta->id]);
        static::assertInstanceOf(ArrayShapeCity::class, $user[$userMeta->city]);
        static::assertSame('Düsseldorf', $user[$userMeta->city][$cityMeta->name]);
    }

    public function testArrayShapeMetaRejectsWrongRuntimeTypes(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('#Invalid type: expected "id" to be of type \{int\}#');

        $userMeta = ArrayShapeUser::meta();
        $user = new ArrayShapeUser([
            $userMeta->id        => 1,
            $userMeta->firstName => 'Lars',
            $userMeta->lastName  => 'Moelleken',
        ]);

        $user[$userMeta->id] = 'wrong-id';
    }
}
