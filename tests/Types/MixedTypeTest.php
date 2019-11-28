<?php

declare(strict_types=1);

use Arrayy\Type\MixedCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MixedTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new MixedCollection([[], true, 1, 1.2, 'A']);

        static::assertSame(
            [[], true, 1, 1.2, 'A'],
            $set->toArray()
        );
    }
}
