<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once \dirname(__DIR__, 2) . '/.phpUnitAndStanFix.php';

new ArrayShapeUser([
    'id' => 'wrong',
    'firstName' => 'Lars',
    'lastName' => 'Moelleken',
]);

new ArrayShapeUser([
    'id' => 1,
    'firstName' => 'Lars',
]);

ArrayShapeUser::create([
    'id' => 'wrong',
    'firstName' => 'Lars',
    'lastName' => 'Moelleken',
]);
