<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\Arrayy;
use Arrayy\TypeCheck\TypeCheckSimple;
use PHPUnit\Framework\TestCase;

/**
 * Executable contracts for the behavioral compatibility decisions in Arrayy 7.11.0.
 *
 * Run only these cases:
 * php vendor/bin/phpunit -c phpunit.xml.dist --group release-7-11 --no-coverage
 *
 * @internal
 * @group release-7-11
 */
final class ReleaseCompatibilityContractTest extends TestCase
{
    /**
     * Every value must satisfy string[], not only the first matching value.
     * The order is intentional: old validation accepted either partially valid array.
     *
     * @dataProvider invalidStringArrayCases
     *
     * @param class-string<CityData|NativeCityData> $modelClass
     * @param list<mixed> $values
     */
    public function testStringArrayRejectsEveryInvalidElement(string $modelClass, array $values): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected "infos" to be of type {string[]}');

        // The instance would exist only if the constructor failed to reject invalid data.
        static::assertInstanceOf($modelClass, new $modelClass([
            'name' => 'Düsseldorf',
            'plz' => null,
            'infos' => $values,
        ]));
    }

    /**
     * @return array<string, array{0: class-string<CityData|NativeCityData>, 1: list<mixed>}>
     */
    public function invalidStringArrayCases(): array
    {
        return [
            'native: invalid value last' => [NativeCityData::class, ['valid', 42]],
            'native: invalid value first' => [NativeCityData::class, [42, 'valid']],
            'native: no valid values' => [NativeCityData::class, [42]],
            'phpdoc: invalid value last' => [CityData::class, ['valid', 42]],
            'phpdoc: invalid value first' => [CityData::class, [42, 'valid']],
            'phpdoc: no valid values' => [CityData::class, [42]],
        ];
    }

    /**
     * Empty arrays and uniformly typed arrays are valid for both native and PHPDoc declarations.
     *
     * @dataProvider validStringArrayCases
     *
     * @param class-string<CityData|NativeCityData> $modelClass
     * @param list<string> $values
     */
    public function testStringArrayAcceptsEmptyAndValidValues(string $modelClass, array $values): void
    {
        $model = new $modelClass([
            'name' => 'Düsseldorf',
            'plz' => null,
            'infos' => $values,
        ]);

        // Nested array properties are returned as Arrayy objects by offset access.
        // Normalize through the public recursive conversion API before comparing values.
        static::assertSame($values, $model->toArray(true)['infos']);
    }

    /**
     * @return array<string, array{0: class-string<CityData|NativeCityData>, 1: list<string>}>
     */
    public function validStringArrayCases(): array
    {
        return [
            'native: empty' => [NativeCityData::class, []],
            'native: populated' => [NativeCityData::class, ['one', 'two']],
            'phpdoc: empty' => [CityData::class, []],
            'phpdoc: populated' => [CityData::class, ['one', 'two']],
        ];
    }

    public function testGenericTypeCheckPreservesMixedNumericCollectionSupport(): void
    {
        // The library also accepts an element union written as float[]|int[].
        $values = [2.5, 2];
        $typeCheck = new TypeCheckSimple('float[]|int[]');

        static::assertTrue($typeCheck->checkType($values));
    }

    /**
     * These exact keys document the choice of SIMPLE Unicode casing.
     * PHP 8.3 contextual sigma handling must not turn the final sigma into ς.
     *
     * @dataProvider unicodeKeyCases
     */
    public function testUnicodeCaseMappingReturnsExactKeys(string $input, int $case, string $expected): void
    {
        $result = Arrayy::create([$input => 'value'])->changeKeyCase($case);

        static::assertSame([$expected => 'value'], $result->toArray());
    }

    /**
     * @return array<string, array{0: string, 1: int, 2: string}>
     */
    public function unicodeKeyCases(): array
    {
        return [
            'Greek final sigma remains normal sigma' => ['ΟΣ', \CASE_LOWER, 'οσ'],
            'Greek multiple sigma remains normal sigma' => ['ΣΣΣ', \CASE_LOWER, 'σσσ'],
            'German sharp s does not expand to SS' => ['Straße', \CASE_UPPER, 'STRAßE'],
            'Turkish capital dotted I does not expand' => ['İ', \CASE_LOWER, 'i'],
        ];
    }

    /**
     * Floating-point keys and null must not be coerced into unrelated integer/string keys.
     * Integer and string keys must remain removable.
     *
     * @dataProvider removalKeyCases
     *
     * @param mixed $key
     * @param array<array-key, string> $initial
     * @param array<array-key, string> $expected
     */
    public function testRemoveDoesNotCoerceUnsupportedKeys($key, array $initial, array $expected): void
    {
        $arrayy = Arrayy::create($initial);
        $result = $arrayy->remove($key);

        static::assertSame($expected, $result->toArray());
        static::assertSame($expected, $arrayy->toArray());
    }

    /**
     * @return array<string, array{0: mixed, 1: array<array-key, string>, 2: array<array-key, string>}>
     */
    public function removalKeyCases(): array
    {
        $original = [1 => 'one', 2 => 'two', '' => 'empty'];

        return [
            'fractional float cannot remove integer key' => [1.5, $original, $original],
            'integral float cannot remove integer key' => [1.0, $original, $original],
            'null cannot remove empty-string key' => [null, $original, $original],
            'integer key still works' => [1, $original, [2 => 'two', '' => 'empty']],
            'numeric string key still works' => ['1', $original, [2 => 'two', '' => 'empty']],
            'empty-string key still works' => ['', $original, [1 => 'one', 2 => 'two']],
        ];
    }

    /**
     * Invalid callbacks must still raise the pre-existing InvalidArgumentException
     * instead of PHP's native TypeError.
     *
     * @dataProvider invalidSortCallbackCases
     *
     * @param mixed $callback
     */
    public function testInvalidSortCallbackKeepsHistoricalException(bool $immutable, $callback): void
    {
        $arrayy = Arrayy::create(['third' => 3, 'first' => 1, 'second' => 2]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Passed function must be callable');

        if ($immutable) {
            $arrayy->uasortImmutable($callback);
        } else {
            $arrayy->uasort($callback);
        }
    }

    /**
     * @return array<string, array{0: bool, 1: mixed}>
     */
    public function invalidSortCallbackCases(): array
    {
        return [
            'mutable: missing function' => [false, 'not_a_callable'],
            'immutable: missing function' => [true, 'not_a_callable'],
            'mutable: null' => [false, null],
            'immutable: null' => [true, null],
        ];
    }

    public function testValidSortCallbackStillSortsAndPreservesAssociativeKeys(): void
    {
        $input = ['third' => 3, 'first' => 1, 'second' => 2];
        $comparator = static function (int $left, int $right): int {
            return $left <=> $right;
        };

        $mutable = Arrayy::create($input);
        static::assertSame(
            ['first' => 1, 'second' => 2, 'third' => 3],
            $mutable->uasort($comparator)->toArray()
        );

        $immutable = Arrayy::create($input);
        $sorted = $immutable->uasortImmutable($comparator);
        static::assertSame(['first' => 1, 'second' => 2, 'third' => 3], $sorted->toArray());
        static::assertSame($input, $immutable->toArray());
    }
}
