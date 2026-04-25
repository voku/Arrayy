<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once __DIR__ . '/../../.phpUnitAndStanFix.php';

/**
 * @internal
 */
final class ArrayShapeAccessTest extends \PHPUnit\Framework\TestCase
{
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
        \PHPStan\Testing\assertType('Arrayy\tests\PHPStan\ArrayShapeCity|null', $user['city']);

        if ($user['city'] !== null) {
            \PHPStan\Testing\assertType('Arrayy\tests\PHPStan\ArrayShapeCity', $user['city']);
            \PHPStan\Testing\assertType('string|null', $user['city']['name']);
            \PHPStan\Testing\assertType('string|null', $user['city']['plz']);
        }

        self::assertSame('Moelleken', $user['lastName']);
        self::assertInstanceOf(ArrayShapeCity::class, $user['city']);
    }
}
