<?php

declare(strict_types=1);

namespace Arrayy\tests\Collection;

use Arrayy\Type\FloatCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CallableTypeTest extends TestCase
{
    public function testArray()
    {
        $set = new \Arrayy\Type\CallableCollection([
            static function () {
                return 'boo';
            },
            'strtolower',
            [\DateTime::class, 'getLastErrors'],
            [\DateTime::class, 'createFromFormat'],
        ]);

        static::assertEquals(
            [
                static function () {
                    return 'boo';
                },
                'strtolower',
                [\DateTime::class, 'getLastErrors'],
                [\DateTime::class, 'createFromFormat'],
            ],
            $set->toArray()
        );
    }

    public function testWrongValue()
    {
        $this->expectException(\TypeError::class);

        new FloatCollection(['strtolower', 1]);
    }
}
