<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

use Arrayy\Arrayy;

/**
 * A string-only callback cannot handle keys produced by unkeyed append().
 *
 * @param Arrayy<string, int> $items
 */
function invalidMutationKeyConsumer(Arrayy $items): void
{
    $items->append(2);
    $items->reject(static fn (int $value, string $key): bool => false);
}
