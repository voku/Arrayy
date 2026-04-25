<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ScalarTypeTest extends TestCase
{
    public function testArray(): void
    {
        $set = new \Arrayy\Type\ScalarCollection([
            '4',
            5.0,
            7,
            true,
            false,
            '6',
            '7',
        ]);

        static::assertEquals(
            [
                '4',
                5.0,
                7,
                true,
                false,
                '6',
                '7',
            ],
            $set->getArray()
        );
    }

    public function testArrayFromJsonMapper(): void
    {
        $set = \Arrayy\Type\ScalarCollection::createFromJsonMapper('["4",5,7,true,false,"6","7"]');

        static::assertEquals(
            [
                '4',
                5.0,
                7,
                true,
                false,
                '6',
                '7',
            ],
            $set->getArray()
        );
    }

    public function testWrongValue(): void
    {
        $this->expectException(\TypeError::class);

        /* @phpstan-ignore argument.type */
        new \Arrayy\Type\ScalarCollection([new \stdClass(), 1]);
    }

    public function testWrongValueFromJsonMapper(): void
    {
        $this->expectException(\TypeError::class);

        \Arrayy\Type\ScalarCollection::createFromJsonMapper('[{},1]');
    }
}
