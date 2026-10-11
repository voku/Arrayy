<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

use Arrayy\Arrayy;

/**
 * Negative fixture. This file must fail PHPStan with two argument.type
 * diagnostics; unlike the positive fixtures, it is analysed separately.
 *
 * @param Arrayy<int, string> $legacy
 */
function consumeLegacyGeneric(Arrayy $legacy): ?string
{
    return $legacy[1];
}

/**
 * @param Arrayy<key-of<T>, value-of<T>, T> $validShape
 * @template T of array{id: int, name: string}
 */
function consumeShapeGeneric(Arrayy $validShape): ?int
{
    return $validShape['id'];
}

// An integer-valued collection is not Arrayy<int, string>.
$legacyResult = consumeLegacyGeneric(new Arrayy([1 => 42]));
\PHPStan\Testing\assertType('string|null', $legacyResult);

// The third generic must preserve an int-valued "id" shape offset.
$shapeResult = consumeShapeGeneric(new Arrayy(['id' => 'wrong', 'name' => 'ok']));
\PHPStan\Testing\assertType('int|null', $shapeResult);
