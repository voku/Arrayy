<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

use Arrayy\Arrayy;
use DateTimeImmutable;

/**
 * Existing two-parameter Arrayy consumers retain typed values, keys and
 * callbacks while IDE-readable @return tags match the PHPStan contracts.
 *
 * @param Arrayy<int, DateTimeImmutable> $dates
 * @param list<DateTimeImmutable> $values
 */
function assertPortableGenericReturns(Arrayy $dates, array $values): void
{
    \PHPStan\Testing\assertType('DateTimeImmutable|null', $dates->first());
    \PHPStan\Testing\assertType('DateTimeImmutable|null', $dates->last());
    \PHPStan\Testing\assertType('DateTimeImmutable|null', $dates->pop());
    \PHPStan\Testing\assertType('DateTimeImmutable|null', $dates->shift());
    \PHPStan\Testing\assertType('DateTimeImmutable|null', $dates->randomValue());
    \PHPStan\Testing\assertType('int|null', $dates->firstKey());
    \PHPStan\Testing\assertType('int|null', $dates->lastKey());
    \PHPStan\Testing\assertType('int|null', $dates->randomKey());
    \PHPStan\Testing\assertType('DateTimeImmutable|false', $dates->find(static fn (DateTimeImmutable $date): bool => $date->getTimestamp() > 0));
    \PHPStan\Testing\assertType('int|false', $dates->findKey(static fn (DateTimeImmutable $date): bool => $date->getTimestamp() > 0));
    foreach ($dates->getIterator() as $date) {
        \PHPStan\Testing\assertType('DateTimeImmutable', $date);
    }
    \PHPStan\Testing\assertType('DateTimeImmutable|null', \Arrayy\array_first($values));
    \PHPStan\Testing\assertType('DateTimeImmutable|null', \Arrayy\array_last($values));
    \PHPStan\Testing\assertType('array<int, DateTimeImmutable>', $dates->getAll());
    \PHPStan\Testing\assertType('list<DateTimeImmutable>', $dates->toList());
    foreach ($dates->getArray() as $date) {
        \PHPStan\Testing\assertType('DateTimeImmutable', $date);
    }
    foreach ($dates->getValuesYield() as $date) {
        \PHPStan\Testing\assertType('DateTimeImmutable', $date);
    }
}

/**
 * The accessor wraps stored PHP arrays, but keeps scalar element types precise.
 *
 * @param Arrayy<int, array<string, int>> $nested
 */
function assertWrappedArrayAccessorReturns(Arrayy $nested): void
{
    \PHPStan\Testing\assertType('Arrayy<int, array<string, int>>|null', $nested->first());
    \PHPStan\Testing\assertType('Arrayy<int, array<string, int>>|null', $nested->last());
    \PHPStan\Testing\assertType('Arrayy<int, array<string, int>>|null', $nested->randomValue());
    \PHPStan\Testing\assertType('Arrayy<int, array<string, int>>|null', $nested->getRandomValue());
}
