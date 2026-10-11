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
            'first' => 'T|null',
            'last' => 'T|null',
            'pop' => 'T|null',
            'shift' => 'T|null',
            'randomValue' => 'T|null',
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
            self::assertStringContainsString('@phpstan-return ' . $expected, $doc, $method);
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
        }
    }
}
