<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once __DIR__ . '/../../.phpUnitAndStanFix.php';

/**
 * @internal
 */
final class AccessWaysTest extends \PHPUnit\Framework\TestCase
{
    public function testAllAccessWaysRetainNestedTypes(): void
    {
        $user = new AccessShapeUser([
            'profile' => new AccessShapeProfile([
                'name' => 'Lars',
            ]),
        ]);

        \PHPStan\Testing\assertType('string|null', $user->get('profile.name'));
        \PHPStan\Testing\assertType('string', $user->get('profile.name', 'Guest'));
        \PHPStan\Testing\assertType('string', $user->get('profile.avatar', 'default.png'));

        \PHPStan\Testing\assertType('Arrayy\tests\PHPStan\AccessShapeProfile', $user['profile']);
        \PHPStan\Testing\assertType('string', $user['profile']['name']);

        \PHPStan\Testing\assertType('Arrayy\tests\PHPStan\AccessShapeProfile', $user->profile);
        \PHPStan\Testing\assertType('string', $user->profile->name);

        self::assertSame('Lars', $user->get('profile.name'));
        self::assertSame('Lars', $user['profile']['name']);
        self::assertSame('Lars', $user->profile->name);
        self::assertSame('default.png', $user->get('profile.avatar', 'default.png'));
    }
}
