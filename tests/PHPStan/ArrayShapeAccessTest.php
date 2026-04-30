<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once __DIR__ . '/../../.phpUnitAndStanFix.php';

/**
 * @internal
 */
final class ArrayShapeAccessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @template TCity of array{name: string, plz?: string|null}
     *
     * @param ArrayShapeCity<TCity>|null $city
     */
    private static function assertNullableCity(?ArrayShapeCity $city): void
    {
    }

    private static function assertNullableString(?string $value): void
    {
    }

    public function testArrayShapeOffsetsAreTyped(): void
    {
        $user = new ArrayShapeUser([
            'id' => 1,
            'firstName' => 'Lars',
            'lastName' => 'Moelleken',
            'city' => new ArrayShapeCity([
                'name' => 'Düsseldorf',
                'plz' => null,
            ]),
        ]);

        \PHPStan\Testing\assertType('int|null', $user['id']);
        \PHPStan\Testing\assertType('string|null', $user['firstName']);
        \PHPStan\Testing\assertType('string|null', $user['lastName']);
        self::assertNullableCity($user['city']);

        if ($user['city'] !== null) {
            self::assertNullableCity($user['city']);
            self::assertNullableString($user['city']['name']);
            self::assertNullableString($user['city']['plz']);
        }

        self::assertSame('Moelleken', $user['lastName']);
        self::assertInstanceOf(ArrayShapeCity::class, $user['city']);
    }
}
