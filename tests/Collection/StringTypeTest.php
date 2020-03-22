<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\StringCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class StringTypeTest extends TestCase
{
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

        new StringCollection(['A', 'B', 'C', 1]);
    }

    public function testWrongValueFromJsonMapper()
    {
        $this->expectException(\TypeError::class);

        StringCollection::createFromJsonMapper('["A","B","C",1]');
    }
}
