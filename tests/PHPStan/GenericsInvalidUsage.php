<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

use Arrayy\Arrayy;

/**
 * Negative fixture: the declared key/value generics contradict the shape.
 *
 * The third argument must be a subtype of array<TKey, T>, so the shape
 * array{id: int} must not be accepted as array<int, string>.
 *
 * @param Arrayy<int, string, array{id: int}> $arrayy
 */
function rejectContradictoryShapeGenerics(Arrayy $arrayy): void
{
}
