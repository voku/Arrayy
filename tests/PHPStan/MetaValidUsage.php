<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once \dirname(__DIR__, 2) . '/.phpUnitAndStanFix.php';

/**
 * @param ArrayShapeCity|null $city
 */
function assertMetaFixtureNullableCity(?ArrayShapeCity $city): void
{
}

/**
 * @param ArrayShapeCity $city
 */
function assertMetaFixtureCity(ArrayShapeCity $city): void
{
}

$cityMeta = ArrayShapeCity::meta();
$userMeta = ArrayShapeUser::meta();

\PHPStan\Testing\assertType("'id'", $userMeta->id);
\PHPStan\Testing\assertType("'city'", $userMeta->city);
\PHPStan\Testing\assertType("'name'", $cityMeta->name);

$user = new ArrayShapeUser([
    $userMeta->id        => 1,
    $userMeta->firstName => 'Lars',
    $userMeta->lastName  => 'Moelleken',
    $userMeta->city      => new ArrayShapeCity([
        $cityMeta->name => 'Düsseldorf',
        $cityMeta->plz  => null,
    ]),
]);

\PHPStan\Testing\assertType('int|null', $user[$userMeta->id]);
assertMetaFixtureNullableCity($user[$userMeta->city]);

if ($user[$userMeta->city] !== null) {
    assertMetaFixtureCity($user[$userMeta->city]);
    \PHPStan\Testing\assertType('string|null', $user[$userMeta->city][$cityMeta->name]);
}
