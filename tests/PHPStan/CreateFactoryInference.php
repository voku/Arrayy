<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

use Arrayy\Arrayy;

use function Arrayy\create;

/**
 * Array inputs keep their key and value types through the namespaced factory.
 *
 * @param array<string, \DateTimeImmutable> $map
 */
function createKeepsAssociativeArrayTypes(array $map): void
{
    $arrayy = create($map);

    \PHPStan\Testing\assertType('Arrayy\Arrayy<string, DateTimeImmutable, array<string, DateTimeImmutable>>', $arrayy);
    \PHPStan\Testing\assertType('DateTimeImmutable|null', $arrayy->first());
    \PHPStan\Testing\assertType('list<DateTimeImmutable>', $arrayy->toList());
    \PHPStan\Testing\assertType('Iterator<string, DateTimeImmutable>', $arrayy->getIterator());

    $arrayy->map(
        static function ($value, $key = '') {
            \PHPStan\Testing\assertType('DateTimeImmutable', $value);
            \PHPStan\Testing\assertType('string', $key);

            return $value;
        }
    );
}

/**
 * @param list<\DateTimeImmutable> $list
 */
function createKeepsListTypes(array $list): void
{
    \PHPStan\Testing\assertType(
        'Arrayy\Arrayy<int<0, max>, DateTimeImmutable, list<DateTimeImmutable>>',
        create($list)
    );
}

/**
 * @param array{id: int, name: string} $shape
 */
function createKeepsArrayShapeTypes(array $shape): void
{
    $arrayy = create($shape);

    \PHPStan\Testing\assertType("Arrayy\Arrayy<'id'|'name', int|string, array{id: int, name: string}>", $arrayy);
    \PHPStan\Testing\assertType('int|string|null', $arrayy->first());
}

/**
 * Non-array inputs stay accepted and keep the untyped fallback.
 *
 * @param \Traversable<int, string> $traversable
 * @param callable(): array<int, string> $callable
 */
function createKeepsFallbackForNonArrayInputs(\Traversable $traversable, callable $callable): void
{
    $fallback = 'Arrayy\Arrayy<int|string, mixed, array<int|string, mixed>>';

    \PHPStan\Testing\assertType($fallback, create($traversable));
    \PHPStan\Testing\assertType($fallback, create($callable));
    \PHPStan\Testing\assertType($fallback, create('scalar'));
    \PHPStan\Testing\assertType($fallback, create(1.5));
    \PHPStan\Testing\assertType($fallback, create(null));
    \PHPStan\Testing\assertType($fallback, create(new \stdClass()));
}

/**
 * An input of unknown type must not be narrowed to an array.
 */
function createDoesNotNarrowMixedInput(mixed $unknown): void
{
    \PHPStan\Testing\assertType('Arrayy\Arrayy<(int|string), mixed, array<mixed>>', create($unknown));
}

/**
 * The result remains usable wherever the documented two-template form is accepted.
 *
 * @param Arrayy<int, string> $arrayy
 */
function consumeLegacyArrayy(Arrayy $arrayy): void
{
    \PHPStan\Testing\assertType('string|null', $arrayy->first());
}

/**
 * @param array<int, string> $strings
 */
function createResultSatisfiesLegacyConsumer(array $strings): void
{
    consumeLegacyArrayy(create($strings));
}
