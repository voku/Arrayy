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
            'firstKey' => 'TKey|int|null',
            'lastKey' => 'TKey|int|null',
            'randomKey' => 'TKey|int|null',
            'find' => 'T|false',
            'findKey' => 'TKey|int|false',
            'indexOf' => 'TKey|int|false',
            'searchIndex' => 'TKey|int|false',
            'getIterator' => '\\Iterator<TKey|int, T>',
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
            'getRandomKey' => ['portable' => 'TKey|int|null', 'phpstan' => 'TKey|int|null'],
            'getRandomValue' => ['portable' => 'T|static|null', 'phpstan' => '(T is array ? static : T)|null'],
            'getValuesYield' => ['portable' => '\\Generator<TKey|int,T>', 'phpstan' => '\\Generator<TKey|int,T>'],
            'getGenerator' => ['portable' => '\\Generator<mixed,T>|\\Generator<TKey|int,T>', 'phpstan' => '\\Generator<mixed,T>|\\Generator<TKey|int,T>'],
            'getBackwardsGenerator' => ['portable' => '\\Generator<mixed,T>|\\Generator<TKey|int,T>', 'phpstan' => '\\Generator<mixed,T>|\\Generator<TKey|int,T>'],
            'getAll' => ['portable' => 'array<TKey|int,T>', 'phpstan' => 'array<TKey|int,T>'],
            'getArray' => ['portable' => 'array<array-key,T|array<array-key,mixed>>|array<TKey|int,T>', 'phpstan' => '($convertAllArrayyElements is true ? array<array-key,T|array<array-key,mixed>> : ($preserveKeys is true ? array<TKey|int,T> : list<T>))'],
            'toArray' => ['portable' => 'array<TKey,T|array<array-key,mixed>>|list<T|array<array-key,mixed>>', 'phpstan' => '($convertAllArrayyElements is true ? ($preserveKeys is true ? array<TKey,T|array<array-key,mixed>> : list<T|array<array-key,mixed>>) : ($preserveKeys is true ? array<TKey|int,T> : list<T>))'],
            'getList' => ['portable' => 'list<T|array<array-key,mixed>>', 'phpstan' => '($convertAllArrayyElements is true ? list<T|array<array-key,mixed>> : list<T>)'],
            'toList' => ['portable' => 'list<T|array<array-key,mixed>>', 'phpstan' => '($convertAllArrayyElements is true ? list<T|array<array-key,mixed>> : list<T>)'],
        ];

        foreach ($types as $method => $expected) {
            $doc = (new ReflectionMethod(Arrayy::class, $method))->getDocComment();
            self::assertIsString($doc, $method);
            self::assertStringContainsString('@return ' . $expected['portable'], $doc, $method);
            self::assertStringContainsString('@phpstan-return ' . $expected['phpstan'], $doc, $method);
        }
    }

    public function testChildConversionKeepsRawValuesByDefaultAndConvertsOnRequest(): void
    {
        $child = new Arrayy(['x' => 1]);
        $parent = new Arrayy(['child' => $child, 'scalar' => 2]);

        self::assertSame($child, $parent->toArray()['child']);
        self::assertSame($child, $parent->getArray()['child']);
        self::assertSame([$child, 2], $parent->toList());
        self::assertSame([$child, 2], $parent->getList());

        $converted = ['child' => ['x' => 1], 'scalar' => 2];
        self::assertSame($converted, $parent->toArray(true));
        self::assertSame($converted, $parent->getArray(true));
        self::assertSame([[1], 2], $parent->toList(true));
        self::assertSame([[1], 2], $parent->getList(true));
    }

    public function testUnkeyedAppendExposesIntegerKeysToConsumers(): void
    {
        $items = new Arrayy(['name' => 1]);
        $items->append(2);

        $seen = [];
        self::assertTrue($items->validate(
            static function (int $value, int|string $key) use (&$seen): bool {
                $seen[$key] = $value;

                return true;
            }
        ));
        self::assertSame(['name' => 1, 0 => 2], $seen);
        self::assertSame(['name' => 1, 0 => 2], iterator_to_array($items->getIterator()));
        self::assertSame(0, $items->findKey(static fn (int $value): bool => $value === 2));
    }

    public function testNarrowStringKeyCallbackCannotHandleUnkeyedAppend(): void
    {
        $items = new Arrayy(['name' => 1]);
        $items->append(2);

        $this->expectException(\TypeError::class);
        $items->reject(static fn (int $value, string $key): bool => false);
    }

    public function testCallableInputsCarryGenericTypesInStandardPhpDoc(): void
    {
        foreach (['find', 'findKey', 'reject', 'validate'] as $method) {
            $doc = (new ReflectionMethod(Arrayy::class, $method))->getDocComment();
            self::assertIsString($doc, $method);
            self::assertStringContainsString('@param \\Closure(T,TKey|int):bool $closure', $doc, $method);
            self::assertStringContainsString('@phpstan-param \\Closure(T,TKey|int):bool $closure', $doc, $method);
        }
    }
}
