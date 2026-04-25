<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\TypeCheck\TypeCheckSimple;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TypeCheckRegressionTest extends TestCase
{
    public function testScalarPseudoTypeFromPhpDocAcceptsScalarValues(): void
    {
        static::assertSame(1, (new DocBlockScalarData(['value' => 1]))['value']);
        static::assertSame('1', (new DocBlockScalarData(['value' => '1']))['value']);
        static::assertSame(1.5, (new DocBlockScalarData(['value' => 1.5]))['value']);
        static::assertTrue((new DocBlockScalarData(['value' => true]))['value']);
    }

    public function testTypeCheckSimpleTrimsUnionSubtypes(): void
    {
        $typeCheck = new TypeCheckSimple(' string | int ');
        $value = 'foo';

        static::assertTrue($typeCheck->checkType($value));
    }

    public function testTypeCheckSimpleTrimsIntersectionSubtypes(): void
    {
        $typeCheck = new TypeCheckSimple(' \Countable & \Traversable ');
        $value = new \ArrayObject(['foo']);

        static::assertTrue($typeCheck->checkType($value));
    }
}
