<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;

/**
 * Class ModelTest
 *
 * @internal
 */
final class ModelTest extends \PHPUnit\Framework\TestCase
{
    public function testDotNotation()
    {
        $model = new ModelA(['foo', 'bar' => ['config' => ['lall' => true]]]);

        static::assertInstanceOf(Arrayy::class, $model);
        static::assertSame('foo', $model[0]);
        static::assertTrue($model['bar^config^lall']); // the separator was changed in the "ModelA"-class
        static::assertNull($model[3]);
    }

    public function testForEach()
    {
        $colors = new ModelB(['red', 'yellow', 'green', 'white']);

        foreach ($colors as $key => $color) {
            if ($key === 0) {
                static::assertSame('red', $color);

                break;
            }
        }

        $colors->natsort();
    }
}
