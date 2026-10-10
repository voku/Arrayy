<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\Arrayy;
use PHPUnit\Framework\TestCase;

/**
 * Executable Arrayy behavior contracts for the 7.11.0 release.
 *
 * @internal
 * @group release-7-11
 */
final class ReleaseCompatibilityArrayyContractTest extends TestCase
{
    /**
     * These exact keys document the choice of SIMPLE Unicode casing.
     * PHP 8.3 contextual sigma handling must not turn the final sigma into ς.
     *
     * @dataProvider unicodeKeyCases
     */
    public function testUnicodeCaseMappingReturnsExactKeys(string $input, int $case, string $expected): void
    {
        $arrayy = new Arrayy([$input => 'value']);
        $result = $arrayy->changeKeyCase($case);

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
        $arrayy = new Arrayy($initial);
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
        $arrayy = new Arrayy(['third' => 3, 'first' => 1, 'second' => 2]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Passed function must be callable');

        if ($immutable) {
            $arrayy->uasortImmutable($callback);

            return;
        }

        $arrayy->uasort($callback);
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

        $mutable = new Arrayy($input);
        static::assertSame(
            ['first' => 1, 'second' => 2, 'third' => 3],
            $mutable->uasort($comparator)->toArray()
        );

        $immutable = new Arrayy($input);
        $sorted = $immutable->uasortImmutable($comparator);
        static::assertSame(['first' => 1, 'second' => 2, 'third' => 3], $sorted->toArray());
        static::assertSame($input, $immutable->toArray());
    }
}
