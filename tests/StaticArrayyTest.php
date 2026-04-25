<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;
use Arrayy\StaticArrayy as A;

/**
 * @internal
 */
final class StaticArrayyTest extends \PHPUnit\Framework\TestCase
{
    public function testBadMethodCall(): void
    {
        $this->expectException(\BadMethodCallException::class);

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUnusedLocalVariableInspection */
        $result = A::invalidMethod('foo'); // @phpstan-ignore staticMethod.notFound
    }

    public function testEmptyArgsInvocation(): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $result = A::first(); // @phpstan-ignore staticMethod.notFound
        static::assertNull($result);
    }

    public function testInvocation(): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $result = A::first(['lall', 'FOOBAR'], 1); // @phpstan-ignore staticMethod.notFound
        static::assertSame('lall', $result);
    }

    public function testPartialArgsInvocation(): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $result = A::replaceOneValue(['foo', 'bar'], 'foo'); // @phpstan-ignore staticMethod.notFound
        static::assertSame(['', 'bar'], $result->getArray());
    }

    public function testFullArgsInvocation(): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $result = A::replaceOneValue(['foo', 'bar'], 'foo', 'test'); // @phpstan-ignore staticMethod.notFound
        static::assertSame(['test', 'bar'], $result->getArray());
    }

    public function testArrayyRange(): void
    {
        $result = A::range(1, null);

        static::assertSame([1], $result->getArray());
    }

    public function testArrayyRange1(): void
    {
        $result = A::range(1, null, 10);

        static::assertSame([1], $result->getArray());
    }

    public function testArrayyRange10(): void
    {
        $result = A::range(1, 10);

        static::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $result->getArray());
    }

    public function testArrayyRange100(): void
    {
        $result = A::range(0, 100, 10);

        static::assertSame([0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100], $result->getArray());
    }

    public function testArrayyRepeat(): void
    {
        $result = A::repeat('foobar', 3);

        static::assertStringContainsString('foobar,foobar,foobar', (string) $result);

        $result = A::repeat('', 3);

        static::assertSame('', (string) $result);
    }

    public function testArrayyRepeatWithArray(): void
    {
        $result = A::repeat(3, 2);

        static::assertStringContainsString('3,3', (string) $result);
    }

    /**
     * Use reflection to ensure that all argument numbers are correct. Each
     * static method should accept 2 more arguments than their Stringy
     * equivalent.
     */
    public function testArgumentNumbers(): void
    {
        $staticArrayyClass = new \ReflectionClass(A::class);
        $arrayyClass = new \ReflectionClass(Arrayy::class);

        // getStaticPropertyValue can't access protected properties
        $properties = $staticArrayyClass->getStaticProperties();

        foreach ((array) $properties['methodArgs'] as $method => $expected) {
            $num = $arrayyClass->getMethod($method)->getNumberOfParameters() + 2;

            static::assertSame($expected, $num, 'Invalid num args for ' . $method);
        }
    }
}
