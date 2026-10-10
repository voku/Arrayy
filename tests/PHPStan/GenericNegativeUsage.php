<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

use Arrayy\Arrayy;

/**
 * Intentionally invalid generic annotations and constructor calls.
 *
 * This file must NOT be added to the default PHPStan analysis paths.
 * The dedicated negative-contract gate runs PHPStan against it and
 * verifies that PHPStan rejects these cases.
 *
 * @param Arrayy<int, string, array{id: int, name: string}> $contradictory
 */
function consumeContradictoryGeneric(Arrayy $contradictory): void
{
    // Read a value using the declared integer-key/string-value contract.
    $contradictory[1];
}

/**
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

/** @var Arrayy<int, string, array{id: int, name: string}> $contradictory */
$contradictory = new Arrayy(['id' => 1, 'name' => 'ok']);
consumeContradictoryGeneric($contradictory);

consumeLegacyGeneric(new Arrayy([1 => 42]));
consumeShapeGeneric(new Arrayy(['id' => 'wrong', 'name' => 'ok']));
