<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once \dirname(__DIR__, 2) . '/.phpUnitAndStanFix.php';

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
\PHPStan\Testing\assertType('Arrayy\tests\PHPStan\ArrayShapeCity<array{name: string, plz: null}>|null', $user['city']);

if ($user['city'] !== null) {
    \PHPStan\Testing\assertType('Arrayy\tests\PHPStan\ArrayShapeCity<array{name: string, plz: null}>', $user['city']);
    \PHPStan\Testing\assertType('string|null', $user['city']['name']);
    \PHPStan\Testing\assertType('null', $user['city']['plz']);
}
