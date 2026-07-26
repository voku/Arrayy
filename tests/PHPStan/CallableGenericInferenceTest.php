<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

require_once __DIR__ . '/../../.phpUnitAndStanFix.php';

/**
 * @internal
 */
final class CallableGenericInferenceTest extends \PHPUnit\Framework\TestCase
{
    public function testEachInfersCallableInputAndOutputTypes(): void
    {
        /** @var CallableGenericArrayy<int,int> $numbers */
        $numbers = CallableGenericArrayy::create([1, 2]);

        $strings = $numbers->each(
            static function ($value, $key): string {
                \PHPStan\Testing\assertType('int', $value);
                \PHPStan\Testing\assertType('int|string|null', $key);

                return $key . ':' . $value;
            }
        );

        \PHPStan\Testing\assertType('Arrayy\tests\PHPStan\CallableGenericArrayy<int, non-falsy-string>', $strings);
        self::assertInstanceOf(CallableGenericArrayy::class, $strings);
        self::assertSame(['0:1', '1:2'], $strings->toArray());
    }

    public function testMapInfersCallableInputAndOutputTypes(): void
    {
        /** @var CallableGenericArrayy<int,int> $numbers */
        $numbers = CallableGenericArrayy::create([1, 2]);

        $strings = $numbers->map(
            static function ($value, $key = null): string {
                \PHPStan\Testing\assertType('int', $value);
                \PHPStan\Testing\assertType('int', $key);

                return $key . ':' . $value;
            },
            true
        );

        \PHPStan\Testing\assertType(
            'Arrayy\tests\PHPStan\CallableGenericArrayy<int, lowercase-string&non-falsy-string&uppercase-string>',
            $strings
        );
        self::assertInstanceOf(CallableGenericArrayy::class, $strings);
        self::assertSame(['0:1', '1:2'], $strings->toArray());
    }
}

/**
 * @template TKey of array-key
 * @template TValue
 * @extends \Arrayy\Arrayy<TKey,TValue,array<TKey,TValue>>
 */
final class CallableGenericArrayy extends \Arrayy\Arrayy
{
}
