<?php

namespace Arrayy\tests;

use Arrayy\StaticArrayy as A;
use Arrayy\Arrayy;

/**
 * Class StaticArrayyTest
 *
 * @internal
 */
final class StaticArrayyTest extends \PHPUnit\Framework\TestCase
{
    public function testBadMethodCall()
    {
        $this->expectException(\BadMethodCallException::class);

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUnusedLocalVariableInspection */
        /** @noinspection OnlyWritesOnParameterInspection */
        $result = A::invalidMethod('foo');
    }

    public function testEmptyArgsInvocation()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $result = A::first();
        static::assertNull($result);
    }

    public function testInvocation()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $result = A::first(['lall', 'FOOBAR'], 1);
        static::assertSame('lall', $result);
    }

    public function testPartialArgsInvocation()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $result = A::replaceOneValue(['foo', 'bar'], 'foo');
        /** @noinspection PhpUndefinedMethodInspection */
        static::assertSame(['', 'bar'], $result->getArray());
    }

    public function testFullArgsInvocation()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $result = A::replaceOneValue(['foo', 'bar'], 'foo', 'test');
        /** @noinspection PhpUndefinedMethodInspection */
        static::assertSame(['test', 'bar'], $result->getArray());
    }

    public function testArrayyRange()
    {
        $result = A::range(1, null);

        static::assertSame([1], $result->getArray());
    }

    public function testArrayyRange1()
    {
        $result = A::range(1, null, 10);

        static::assertSame([1], $result->getArray());
    }

    public function testArrayyRange10()
    {
        $result = A::range(1, 10);

        static::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $result->getArray());
    }

    public function testArrayyRange100()
    {
        $result = A::range(0, 100, 10);

        static::assertSame([0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100], $result->getArray());
    }

    public function testArrayyRepeat()
    {
        $result = A::repeat('foobar', 3);

        static::assertContains('foobar,foobar,foobar', (string) $result);

        $result = A::repeat('', 3);

        static::assertSame('', (string) $result);
    }

    public function testArrayyRepeatWithArray()
    {
        $result = A::repeat(3, 2);

        static::assertContains('3,3', (string) $result);
    }

    /**
     * Use reflection to ensure that all argument numbers are correct. Each
     * static method should accept 2 more arguments than their Stringy
     * equivalent.
     */
    public function testArgumentNumbers()
    {
        $staticArrayyClass = new \ReflectionClass(A::class);
        $arrayyClass = new \ReflectionClass(Arrayy::class);

        // getStaticPropertyValue can't access protected properties
        $properties = $staticArrayyClass->getStaticProperties();

        foreach ( (array) $properties['methodArgs'] as $method => $expected) {
            $num = $arrayyClass->getMethod($method)->getNumberOfParameters() + 2;

            static::assertSame($expected, $num, 'Invalid num args for ' . $method);
        }
    }
}
