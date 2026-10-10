<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

use Arrayy\Arrayy;

/**
 * Existing consumers can still extend Arrayy with two type arguments.
 *
 * @extends Arrayy<int, string>
 */
final class LegacyTwoParameterArrayy extends Arrayy
{
    /**
     * @param array<int, string> $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}

/**
 * Existing consumers can still use two-argument Arrayy parameter types.
 *
 * @param Arrayy<int, string> $arrayy
 */
function assertLegacyTwoArgumentArrayy(Arrayy $arrayy): void
{
    \PHPStan\Testing\assertType('string|null', $arrayy[0]);
}

/**
 * The three-argument array-shape form must keep its precise offset type.
 *
 * @param Arrayy<key-of<T>, value-of<T>, T> $arrayy
 *
 * @template T of array{id: int, name: string}
 */
function assertExplicitArrayShapeArrayy(Arrayy $arrayy): void
{
    \PHPStan\Testing\assertType('int|null', $arrayy['id']);
}
