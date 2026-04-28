<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once \dirname(__DIR__, 2) . '/.phpUnitAndStanFix.php';

$cityMeta = ArrayShapeCity::meta();
$userMeta = ArrayShapeUser::meta();

$user = new ArrayShapeUser([
    $userMeta->id        => 1,
    $userMeta->firstName => 'Lars',
    $userMeta->lastName  => 'Moelleken',
    $userMeta->city      => new ArrayShapeCity([
        $cityMeta->name => 'Düsseldorf',
        $cityMeta->plz  => null,
    ]),
]);

$ghost = $userMeta->ghost;
$length = \strlen($user[$userMeta->id]);
