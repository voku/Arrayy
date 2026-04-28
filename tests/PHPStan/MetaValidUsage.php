<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once \dirname(__DIR__, 2) . '/.phpUnitAndStanFix.php';

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
\PHPStan\Testing\assertType('Arrayy\tests\PHPStan\ArrayShapeCity<array{name: string, plz: null}>|null', $user[$userMeta->city]);

if ($user[$userMeta->city] !== null) {
    \PHPStan\Testing\assertType('Arrayy\tests\PHPStan\ArrayShapeCity<array{name: string, plz: null}>', $user[$userMeta->city]);
    \PHPStan\Testing\assertType('string|null', $user[$userMeta->city][$cityMeta->name]);
}
