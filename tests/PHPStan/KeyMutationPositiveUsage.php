<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

use Arrayy\Arrayy;

/**
 * @param Arrayy<string, int> $items
 */
function validMutationKeyConsumer(Arrayy $items): void
{
    $items->append(2);
    $items->reject(static fn (int $value, int|string $key): bool => $value === 2);
    $items->validate(static fn (int $value, int|string $key): bool => $value > 0);
    $items->find(static fn (int $value, int|string $key): bool => $key === 0);
    $items->findKey(static fn (int $value, int|string $key): bool => $key === 0);
    foreach ($items->getIterator() as $key => $value) {
        $acceptKey = static fn (int|string $key): bool => true;
        $acceptKey($key);
    }
}
