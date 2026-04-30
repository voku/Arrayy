<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once \dirname(__DIR__, 2) . '/.phpUnitAndStanFix.php';

/**
 * @template TCity of array{name: string, plz?: string|null}
 *
 * @param ArrayShapeCity<TCity>|null $city
 */
function assertValidArrayShapeNullableCity(?ArrayShapeCity $city): void
{
}

/**
 * @template TCity of array{name: string, plz?: string|null}
 *
 * @param ArrayShapeCity<TCity> $city
 */
function assertValidArrayShapeCity(ArrayShapeCity $city): void
{
}

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
assertValidArrayShapeNullableCity($user['city']);

if ($user['city'] !== null) {
    assertValidArrayShapeCity($user['city']);
    \PHPStan\Testing\assertType('string|null', $user['city']['name']);
    \PHPStan\Testing\assertType('null', $user['city']['plz']);
}
