<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\StringCollection;
use Arrayy\Type\StringCollection as PhpString;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class StringTypeTest extends TestCase
{
    public function testArraySimple(): void
    {
        $this->expectException(\TypeError::class);

        $strings = PhpString::create();

        $strings[] = 'A';
        $strings[] = 'B';
        $strings[] = 'C';
        $strings[] = 1.0; // @phpstan-ignore-line offsetAssign.valueType (the test intentionally verifies runtime rejection of an invalid collection value)
    }

    public function testArray(): void
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testArrayFromJsonMapper(): void
    {
        $json = '["A","B","C","D"]';

        $set = StringCollection::createFromJsonMapper($json);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testWrongValue(): void
    {
        $this->expectException(\TypeError::class);

        new StringCollection(['A', 'B', 'C', 1]); // @phpstan-ignore-line argument.type (the runtime API intentionally accepts or transforms a value PHPStan cannot reconcile with the invariant template)
    }

    public function testWrongValueFromJsonMapper(): void
    {
        $this->expectException(\TypeError::class);

        StringCollection::createFromJsonMapper('["A","B","C",1]');
    }
}
