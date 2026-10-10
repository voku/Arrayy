<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\TypeCheck\TypeCheckSimple;
use PHPUnit\Framework\TestCase;

/**
 * Executable type-validation contracts for the Arrayy 7.11.0 release.
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
}
