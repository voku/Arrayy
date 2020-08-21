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
    public function testArraySimple()
    {
        $this->expectException(\TypeError::class);

        $strings = PhpString::create();

        $strings[] = 'A';
        $strings[] = 'B';
        $strings[] = 'C';
        $strings[] = 1.0;
    }

    public function testArray()
    {
        $set = new StringCollection(['A', 'B', 'C', 'D']);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testArrayFromJsonMapper()
    {
        $json = '["A","B","C","D"]';

        $set = StringCollection::createFromJsonMapper($json);

        static::assertSame(
            ['A', 'B', 'C', 'D'],
            $set->toArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        /** @phpstan-ignore-next-line */
        new StringCollection(['A', 'B', 'C', 1]);
    }

    public function testWrongValueFromJsonMapper()
    {
        $this->expectException(\TypeError::class);

        StringCollection::createFromJsonMapper('["A","B","C",1]');
    }
}
