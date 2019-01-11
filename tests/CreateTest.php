<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;

/**
 * Class CreateTestCase
 *
 * @internal
 */
final class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $arrayy = \Arrayy\create(['foo bar', 'UTF-8']);

        static::assertInstanceOf(Arrayy::class, $arrayy);
        static::assertSame('foo bar,UTF-8', (string) $arrayy);
        static::assertSame('foo bar', $arrayy[0]);
        static::assertSame('UTF-8', $arrayy[1]);
        static::assertNull($arrayy[3]);

        foreach ($arrayy as $key => $value) {
            if ($key === 0) {
                static::assertSame('foo bar', $arrayy[$key]);
            } elseif ($key === 1) {
                static::assertSame('UTF-8', $arrayy[$key]);
            }
        }
    }
}
