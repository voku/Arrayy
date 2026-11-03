<?php

declare(strict_types=1);

namespace Arrayy\Tests\Consumer;

use Arrayy\Arrayy;
use DateTimeImmutable;
use function PHPStan\Testing\assertType;

/**
 * Installed from a separate Composer project, not autoload-dev from the repo.
 *
 * @param Arrayy<int, DateTimeImmutable> $dates
 * @param list<DateTimeImmutable> $values
 */
function checkGenericConsumer(Arrayy $dates, array $values): void
{
    assertType('DateTimeImmutable|null', $dates->first());
    assertType('int|null', $dates->firstKey());
    assertType('DateTimeImmutable|false', $dates->find(
        static fn (DateTimeImmutable $date): bool => $date->getTimestamp() > 0
    ));
    assertType('array<int, DateTimeImmutable>', $dates->getAll());
    assertType('list<DateTimeImmutable>', $dates->toList());
    assertType('DateTimeImmutable|null', \Arrayy\array_first($values));
    assertType('DateTimeImmutable|null', \Arrayy\array_last($values));

    foreach ($dates->getIterator() as $value) {
        assertType('DateTimeImmutable', $value);
    }
}
