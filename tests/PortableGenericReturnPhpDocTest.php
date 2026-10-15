<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\Arrayy;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Verify standard PHPDoc exposes the same generic returns to IDEs as PHPStan.
 *
 * @internal
 */
final class PortableGenericReturnPhpDocTest extends TestCase
{
    public function testPublicElementAccessReturnTypesArePortable(): void
    {
        /** @var array<string, string> $types */
        $types = [
            'first' => 'T|static|null',
            'last' => 'T|static|null',
            'pop' => 'T|null',
            'shift' => 'T|null',
            'randomValue' => 'T|static|null',
            'firstKey' => 'TKey|null',
            'lastKey' => 'TKey|null',
            'randomKey' => 'TKey|null',
            'find' => 'T|false',
            'findKey' => 'TKey|false',
            'indexOf' => 'TKey|false',
            'searchIndex' => 'TKey|false',
            'getIterator' => '\\Iterator<TKey, T>',
        ];

        foreach ($types as $method => $expected) {
            $doc = (new ReflectionMethod(Arrayy::class, $method))->getDocComment();
            self::assertIsString($doc, $method);
            self::assertStringContainsString('@return ' . $expected, $doc, $method);
            $phpstanType = \in_array($method, ['first', 'last', 'randomValue'], true)
                ? '(T is array ? static : T)|null'
                : $expected;
            self::assertStringContainsString('@phpstan-return ' . $phpstanType, $doc, $method);
        }
    }

    public function testNestedArrayAccessorsReturnWrappedArrayyValues(): void
    {
        $values = new Arrayy([['value' => 42]]);

        foreach (['first', 'last', 'randomValue', 'getRandomValue'] as $method) {
            $wrapped = $values->{$method}();
            self::assertInstanceOf(Arrayy::class, $wrapped, $method);
            self::assertSame(['value' => 42], $wrapped->toArray(), $method);
        }
    }

    public function testNamespaceHelpersExposeGenericFallbackTypes(): void
    {
        foreach ([
            'array_first' => 'TFirst|TFirstFallback',
            'array_last' => 'TLast|TLastFallback',
        ] as $function => $expected) {
            $doc = (new ReflectionFunction('Arrayy\\' . $function))->getDocComment();
            self::assertIsString($doc, $function);
            self::assertStringContainsString('@return ' . $expected, $doc, $function);
            self::assertStringContainsString('@phpstan-return ' . $expected, $doc, $function);
            $template = $function === 'array_first' ? 'TFirst' : 'TLast';
            self::assertStringContainsString('@param ' . $template . '[] $array', $doc, $function);
            self::assertStringContainsString('@param ' . $template . 'Fallback $fallback', $doc, $function);
            self::assertStringContainsString('@phpstan-param ' . $template . '[] $array', $doc, $function);
            self::assertStringContainsString('@phpstan-param ' . $template . 'Fallback $fallback', $doc, $function);
        }
    }

    public function testArrayAndGeneratorReturnsRetainGenericValues(): void
    {
        /** @var array<string, array{portable: string, phpstan: string}> $types */
        $types = [
            'getRandomKey' => ['portable' => 'TKey|null', 'phpstan' => 'TKey|null'],
            'getRandomValue' => ['portable' => 'T|static|null', 'phpstan' => '(T is array ? static : T)|null'],
            'getValuesYield' => ['portable' => '\\Generator<TKey,T>', 'phpstan' => '\\Generator<TKey,T>'],
            'getGenerator' => ['portable' => '\\Generator<mixed,T>|\\Generator<TKey,T>', 'phpstan' => '\\Generator<mixed,T>|\\Generator<TKey,T>'],
            'getBackwardsGenerator' => ['portable' => '\\Generator<mixed,T>|\\Generator<TKey,T>', 'phpstan' => '\\Generator<mixed,T>|\\Generator<TKey,T>'],
            'getAll' => ['portable' => 'array<TKey,T>', 'phpstan' => 'array<TKey,T>'],
            'getArray' => ['portable' => 'array<array-key,T>|array<TKey,T>', 'phpstan' => 'array<array-key,T>|array<TKey,T>'],
            'toArray' => ['portable' => 'array<TKey,T>|list<T>', 'phpstan' => '($preserveKeys is true ? array<TKey,T> : T[])'],
            'toList' => ['portable' => 'list<T>', 'phpstan' => 'list<T>'],
        ];

        foreach ($types as $method => $expected) {
            $doc = (new ReflectionMethod(Arrayy::class, $method))->getDocComment();
            self::assertIsString($doc, $method);
            self::assertStringContainsString('@return ' . $expected['portable'], $doc, $method);
            self::assertStringContainsString('@phpstan-return ' . $expected['phpstan'], $doc, $method);
        }
    }

    public function testCallableInputsCarryGenericTypesInStandardPhpDoc(): void
    {
        foreach (['find', 'findKey', 'reject', 'validate'] as $method) {
            $doc = (new ReflectionMethod(Arrayy::class, $method))->getDocComment();
            self::assertIsString($doc, $method);
            self::assertStringContainsString('@param \\Closure(T,TKey):bool $closure', $doc, $method);
            self::assertStringContainsString('@phpstan-param \\Closure(T,TKey):bool $closure', $doc, $method);
        }
    }
}
