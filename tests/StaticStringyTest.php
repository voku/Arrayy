<?php

use Stringy\StaticStringy as S;

require_once __DIR__ . '/../src/StaticStringy.php';

use Stringy\Stringy;

/**
 * Class StaticStringyTest
 *
 * @internal
 */
final class StaticStringyTest extends \PHPUnit\Framework\TestCase
{
    public function testBadMethodCall()
    {
        $this->expectException(\BadMethodCallException::class);

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUnusedLocalVariableInspection */
        /** @noinspection OnlyWritesOnParameterInspection */
        $result = S::invalidMethod('foo');
    }

    public function testEmptyArgsInvocation()
    {
        /** @noinspection PhpParamsInspection */
        $result = S::toLowerCase();
        static::assertSame('', (string) $result);
    }

    public function testInvocation()
    {
        $result = S::toLowerCase('FOOBAR');
        static::assertSame('foobar', (string) $result);
    }

    public function testPartialArgsInvocation()
    {
        $result = S::slice('foobar', 0, 3);
        static::assertSame('foo', (string) $result);
    }

    public function testFullArgsInvocation()
    {
        $result = S::slice('fòôbàř', 0, 3, 'UTF-8');
        static::assertSame('fòô', (string) $result);
    }

    /**
     * Use reflection to ensure that all argument numbers are correct. Each
     * static method should accept 2 more arguments than their Stringy
     * equivalent.
     */
    public function testArgumentNumbers()
    {
        $staticStringyClass = new ReflectionClass(S::class);
        $stringyClass = new ReflectionClass(Stringy::class);

        // getStaticPropertyValue can't access protected properties
        $properties = $staticStringyClass->getStaticProperties();

        foreach ($properties['methodArgs'] as $method => $expected) {
            $num = $stringyClass->getMethod($method)->getNumberOfParameters() + 2;

            static::assertSame($expected, $num, 'Invalid num args for ' . $method);
        }
    }
}
